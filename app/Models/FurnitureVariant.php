<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FurnitureVariant extends Model
{
    protected $fillable = [
        'furniture_id',
        'size',
        'quantity',
        'price',
    ];

    public function furniture(): BelongsTo
    {
        return $this->belongsTo(Furniture::class);
    }
}