<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Furniture;
use App\Models\FurnitureVariant;
use App\Models\Order;

class OrderItem extends Model
{
    use HasFactory;

    // Đối chiếu tên cột với bảng order_items trong database
    protected $fillable = [
        'order_id',
        'furniture_id',
        'furniture_variant_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ controller đang gọi: items.furniture
    public function furniture()
    {
        return $this->belongsTo(Furniture::class, 'furniture_id');
    }

    // Giữ lại tên cũ để các chỗ khác đang dùng $item->product không bị lỗi
    public function product()
    {
        return $this->belongsTo(Furniture::class, 'furniture_id');
    }

    // Quan hệ controller đang gọi: items.variant
    public function variant()
    {
        return $this->belongsTo(FurnitureVariant::class, 'furniture_variant_id');
    }
}