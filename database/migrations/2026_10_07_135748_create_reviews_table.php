<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('furniture_id')->constrained('furnitures')->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('rating')->default(5); // Số sao từ 1 đến 5
            $table->text('comment')->nullable(); // Nội dung đánh giá
            $table->timestamps();

            // Đảm bảo 1 user chỉ được đánh giá 1 sản phẩm trong 1 đơn hàng cụ thể 1 lần
            $table->unique(['user_id', 'furniture_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};