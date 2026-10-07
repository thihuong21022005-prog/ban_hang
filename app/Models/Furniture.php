<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Furniture extends Model
{
    protected $table = 'furnitures';

    // Đã thêm 'quantity' và 'price' vào đây
    protected $fillable = [
        'category_id', 
        'name', 
        'price',
        'quantity', 
        'main_image', 
        'is_featured', 
        'description'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function colors()
    {
        return $this->hasMany(FurnitureColor::class, 'furniture_id');
    }
    // Thêm vào trong class Furniture
public function variants()
{
    return $this->hasMany(FurnitureVariant::class, 'furniture_id');
}
// Thêm vào trong class Furniture
public function reviews()
{
    return $this->hasMany(Review::class);
}

// Hàm hỗ trợ lấy điểm đánh giá trung bình
public function averageRating()
{
    return round($this->reviews()->avg('rating') ?? 0, 1);
}
}