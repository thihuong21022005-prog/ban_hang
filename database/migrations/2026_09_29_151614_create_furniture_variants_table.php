<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tạo bảng chứa kích thước và tồn kho
        if (!Schema::hasTable('furniture_variants')) {
            Schema::create('furniture_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('furniture_id')->constrained('furnitures')->onDelete('cascade');
                $table->string('size');
                $table->integer('quantity')->default(0);
                $table->decimal('price', 15, 2)->nullable();
                $table->timestamps();
            });
        }

        // 2. Bổ sung cột biến thể vào bảng order_items (Bỏ phần ->after(...) để tránh lỗi cột)
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'furniture_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->foreignId('furniture_variant_id')->nullable()->constrained('furniture_variants')->onDelete('set null');
                $table->string('variant_size')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['furniture_variant_id']);
                $table->dropColumn(['furniture_variant_id', 'variant_size']);
            });
        }
        Schema::dropIfExists('furniture_variants');
    }
};