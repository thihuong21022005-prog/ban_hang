<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    // Chỉ định chính xác tên bảng trong Database
    protected $table = 'payment_transactions';

    protected $fillable = [
        'order_id',
        'gateway',
        'gateway_order_id',
        'transaction_id',
        'amount',
        'status',
        'result_code',
        'message',
        'request_payload',
        'response_payload',
        'paid_at',
    ];

    /**
     * Ẩn thông tin payload thô khi biến Model thành JSON/Array (Tránh lộ token hoặc dữ liệu nhạy dụng)
     */
    protected $hidden = [
        'request_payload',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'request_payload' => 'array',
            'response_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /* --- Mối quan hệ (Relationships) --- */

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /* --- Bộ lọc truy vấn (Query Scopes) --- */

    // Lọc nhanh giao dịch đã thanh toán: PaymentTransaction::paid()->get()
    public function scopePaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['paid', 'completed', 'success']);
    }

    // Lọc nhanh giao dịch chờ: PaymentTransaction::pending()->get()
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /* --- Hàm tiện ích (Helper Methods) --- */

    // Kiểm tra nhanh xem giao dịch này đã xong chưa
    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'completed', 'success']);
    }

    // Đánh dấu đã thanh toán thành công (dùng gọn trong MoMo / VNPAY Callback)
    public function markAsPaid(?string $transactionId = null, array $responsePayload = []): bool
    {
        return $this->update([
            'status'           => 'paid',
            'transaction_id'   => $transactionId ?? $this->transaction_id,
            'response_payload' => $responsePayload ?: $this->response_payload,
            'paid_at'          => now(),
        ]);
    }
}