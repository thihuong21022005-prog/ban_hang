<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // furniture_id: đổi tên nếu đang là product_id, nếu chưa có thì thêm mới
        if (!Schema::hasColumn('order_items', 'furniture_id')) {
            if (Schema::hasColumn('order_items', 'product_id')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->renameColumn('product_id', 'furniture_id');
                });
            } else {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->unsignedBigInteger('furniture_id')->nullable()->after('order_id');
                });
            }
        }

        // furniture_variant_id: thêm nếu chưa có
        if (!Schema::hasColumn('order_items', 'furniture_variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('furniture_variant_id')->nullable()->after('furniture_id');
            });
        }
    }

    public function down(): void
    {
        // Không cần rollback
    }
};