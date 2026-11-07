<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MomoService
{
    public function createPayment(Order $order, PaymentTransaction $transaction): array
    {
        $endpoint = config('services.momo.endpoint') ?: env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code') ?: env('MOMO_PARTNER_CODE', '');
        $accessKey = config('services.momo.access_key') ?: env('MOMO_ACCESS_KEY', '');
        $secretKey = config('services.momo.secret_key') ?: env('MOMO_SECRET_KEY', '');

        $orderInfo = 'Thanh toan don hang #' . $order->id;
        $amount = (string) ((int) $order->total_price);
        $orderId = $order->id . $transaction->id . time();
        $redirectUrl = config('services.momo.redirect_url') ?: route('user.payment.momo.callback');
        $ipnUrl = config('services.momo.ipn_url') ?: route('payment.momo.ipn');
        $extraData = (string) $order->id;
        $requestId = (string) time();

        $requestType = config('services.momo.request_type') ?: env('MOMO_REQUEST_TYPE', 'captureWallet');

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Fruit Shop',
            'storeId'     => 'MomoStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => hash_hmac('sha256', $rawHash, $secretKey),
        ];

        $transaction->update([
            'gateway_order_id' => $orderId,
            'request_payload'  => $data,
        ]);

        try {
            $payloadJson = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $ch = curl_init($endpoint);
            
            $headers = [
                'Content-Type: application/json; charset=UTF-8',
                'Content-Length: ' . strlen($payloadJson),
                'Accept: application/json, text/plain, */*',
                'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Origin: https://ban-hang-nnm3.onrender.com',
                'Referer: https://ban-hang-nnm3.onrender.com/',
            ];

            $curlOptions = [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payloadJson,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1, // Ép dùng HTTP 1.1 tránh Nginx WAF từ chối
                CURLOPT_HTTPHEADER     => $headers,
            ];

            $proxy = env('MOMO_PROXY');
            if (!empty($proxy)) {
                $curlOptions[CURLOPT_PROXY] = $proxy;
            }

            curl_setopt_array($ch, $curlOptions);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $result = json_decode($responseBody, true);

            if (!is_array($result) || !isset($result['payUrl'])) {
                Log::error('MoMo raw response', [
                    'status'       => $httpCode,
                    'curl_error'   => $curlError,
                    'body'         => $responseBody,
                    'endpoint'     => $endpoint,
                    'partner_code' => $partnerCode,
                    'request_type' => $requestType,
                ]);
            }

            if (!is_array($result)) {
                $result = ['message' => 'MoMo HTTP ' . $httpCode . ' (không phải JSON): ' . strip_tags($responseBody)];
            }
        } catch (\Throwable $e) {
            Log::error('MoMo HTTP Exception:', ['error' => $e->getMessage()]);
            $result = ['message' => 'Lỗi kết nối MoMo HTTP: ' . $e->getMessage()];
        }

        $transaction->update([
            'response_payload' => $result,
            'result_code'      => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
            'message'          => $result['message'] ?? null,
            'status'           => isset($result['payUrl']) ? 'initiated' : 'failed',
        ]);

        return $result;
    }

    public function isSuccessful(array $payload): bool
    {
        return (string) ($payload['resultCode'] ?? '') === '0';
    }

    public function markPaid(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => (int) ($payload['resultCode'] ?? 0),
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'paid',
            'paid_at'          => Carbon::now(),
        ]);
    }

    public function markFailed(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id'   => $payload['transId'] ?? null,
            'result_code'      => isset($payload['resultCode']) ? (int) $payload['resultCode'] : null,
            'message'          => $payload['message'] ?? null,
            'response_payload' => $payload,
            'status'           => 'failed',
        ]);
    }

   public function isValidSuccessfulResponse(array $payload): bool
{
    return $this->isValidResponse($payload) && $this->isSuccessful($payload);
}

    public function isValidResponse(array $payload): bool
    {
        if (!isset($payload['signature'])) {
            return false;
        }

        $accessKey = config('services.momo.access_key') ?: env('MOMO_ACCESS_KEY', '');
        $secretKey = config('services.momo.secret_key') ?: env('MOMO_SECRET_KEY', '');

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . ($payload['amount'] ?? '') .
            '&extraData=' . ($payload['extraData'] ?? '') .
            '&message=' . ($payload['message'] ?? '') .
            '&orderId=' . ($payload['orderId'] ?? '') .
            '&orderInfo=' . ($payload['orderInfo'] ?? '') .
            '&orderType=' . ($payload['orderType'] ?? '') .
            '&partnerCode=' . ($payload['partnerCode'] ?? '') .
            '&payType=' . ($payload['payType'] ?? '') .
            '&requestId=' . ($payload['requestId'] ?? '') .
            '&responseTime=' . ($payload['responseTime'] ?? '') .
            '&resultCode=' . ($payload['resultCode'] ?? '') .
            '&transId=' . ($payload['transId'] ?? '');

        return hash_equals(
            hash_hmac('sha256', $rawHash, $secretKey),
            (string) $payload['signature']
        );
    }

    public function orderId(array $payload): ?int
    {
        $orderId = $payload['extraData'] ?? null;
        return is_numeric($orderId) ? (int) $orderId : null;
    }
}