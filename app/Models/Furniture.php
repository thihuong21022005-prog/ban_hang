<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Furniture extends Model
{
    protected $table = 'furnitures';

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

    public function variants()
    {
        return $this->hasMany(FurnitureVariant::class, 'furniture_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function averageRating()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    // Trả về URL/base64 ảnh để dùng trực tiếp trong <img src="...">
    public function getImageUrlAttribute()
    {
        $path = $this->main_image;
        if (!$path) {
            return null;
        }

        return (str_starts_with($path, 'http') || str_starts_with($path, 'data:'))
            ? $path
            : asset('storage/' . $path);
    }
}