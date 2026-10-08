<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE furnitures MODIFY main_image LONGTEXT NULL');
    }

    public function down(): void
    {
        // Không rollback để tránh cắt mất dữ liệu ảnh
    }
};