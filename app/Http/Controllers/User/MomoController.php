<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Furniture; // <<-- Bổ sung Model Furniture
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    public function start(Order $order, MomoService $momo)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    public function payAgain(Order $order, MomoService $momo)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    public function callback(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo callback received', [
            'payload' => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        if (!$momo->isValidSuccessfulResponse($request->all())) {
            Log::warning('MoMo callback rejected', [
                'result_code' => $request->input('resultCode'),
                'order_id' => $request->input('orderId'),
                'signature_valid' => $momo->isValidResponse($request->all()),
            ]);

            if ($momo->isValidResponse($request->all())) {
                $this->markFailed($request->all(), $momo);
            }

            return redirect()->route('user.orders.index')->with('error', 'Giao dịch MoMo thất bại. Số lượng hàng đã được hoàn lại vào kho.');
        }

        $result = $this->completePayment($request->all(), $ghnOrders, $momo);
        $message = in_array($result, ['created', 'already_created'], true)
            ? 'Thanh toán MoMo thành công! Vận đơn GHN đã được khởi tạo.'
            : 'Thanh toán thành công! Đơn hàng đang chờ tạo vận đơn GHN.';

        return redirect()->route('user.orders.index')->with('success', $message);
    }

    public function ipn(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo IPN received', [
            'payload' => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        if ($momo->isValidSuccessfulResponse($request->all())) {
            $this->completePayment($request->all(), $ghnOrders, $momo);
        } elseif ($momo->isValidResponse($request->all())) {
            $this->markFailed($request->all(), $momo);
        }

        return response()->json(['message' => 'Received']);
    }

    private function newTransaction(Order $order): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => $order->total_price,
            'status' => 'pending',
        ]);
    }

    private function redirectToMomo(Order $order, PaymentTransaction $transaction, MomoService $momo)
    {
        $result = $momo->createPayment($order, $transaction);
        return isset($result['payUrl'])
            ? redirect($result['payUrl'])
            : redirect()->route('user.orders.index')->with('error', 'Không thể kết nối tới MoMo.');
    }

    private function completePayment(array $payload, GHNOrderService $ghnOrders, MomoService $momo): string
    {
        $result = DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return 'invalid';
            }

            $order = Order::lockForUpdate()->find($transaction->order_id);
            if (!$order) {
                return 'invalid';
            }

            if ($order->ghn_order_code) {
                return 'already_created';
            }

            if ($order->shipping_status === 'processing') {
                return 'processing';
            }

            if ((int) $transaction->amount !== (int) ($payload['amount'] ?? 0)) {
                $momo->markFailed($transaction, $payload);
                return 'invalid';
            }

            // Cập nhật trạng thái đơn thành công
            $order->update([
                'status' => 'paid',
                'shipping_status' => 'processing',
                'payment_method' => 'momo', 
            ]);

            $momo->markPaid($transaction, $payload);

            return ['create', $order->id];
        });

        if (!is_array($result)) {
            return (string) $result;
        }

        $order = Order::with('items')->find($result[1]);
        $response = $ghnOrders->create($order, true);

        if (isset($response['code']) && $response['code'] === 200) {
            $order->update([
                'ghn_order_code' => $response['data']['order_code'],
                'shipping_status' => 'ready_to_pick',
            ]);
            return 'created';
        }

        Log::error('GHN order failed after MoMo payment', [
            'order_id' => $order->id,
            'response' => $response,
        ]);

        $order->update(['shipping_status' => 'pending']);
        return 'failed';
    }

    /**
     * XỬ LÝ KHI GIAO DỊCH MOMO THẤT BẠI HOẶC BỊ HỦY -> HOÀN LẠI TỒN KHO
     */
    private function markFailed(array $payload, MomoService $momo): void
    {
        DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if ($transaction && $transaction->status !== 'paid') {
                $momo->markFailed($transaction, $payload);

                // Lấy đơn hàng tương ứng
                $order = Order::with('items')->lockForUpdate()->find($transaction->order_id);

                if ($order && !in_array($order->status, ['canceled', 'failed'])) {
                    // 1. Chuyển trạng thái đơn sang 'canceled'
                    $order->update(['status' => 'canceled']);

                    // 2. HOÀN LẠI SỐ LƯỢNG KHO CHO CÁC SẢN PHẨM TRONG ĐƠN
                    foreach ($order->items as $item) {
                        $productId = $item->product_id ?? $item->furniture_id;
                        if ($productId) {
                            Furniture::where('id', $productId)->increment('quantity', $item->quantity);
                        }
                    }
                }
            }
        });
    }
}