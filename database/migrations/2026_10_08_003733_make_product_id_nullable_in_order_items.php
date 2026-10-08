<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('order_items', 'product_id')) {
            DB::statement('ALTER TABLE order_items MODIFY product_id BIGINT UNSIGNED NULL');
        }

        // Cột cũ tương tự (nếu có) cũng cho phép NULL
        if (Schema::hasColumn('order_items', 'variant_id')) {
            DB::statement('ALTER TABLE order_items MODIFY variant_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Không cần rollback
    }
};