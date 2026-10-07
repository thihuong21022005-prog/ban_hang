<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'total_price',
        'status',
        'shipping_status',
        'payment_method', // <<-- ĐÃ THÊM CỘT NÀY VÀO ĐÂY
        'ghn_order_code',
        'ghn_total_fee',
        'to_district_id',
        'to_ward_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function paymentTransactions()
    {
        return $this->hasMany(PaymentTransaction::class, 'order_id');
    }
}