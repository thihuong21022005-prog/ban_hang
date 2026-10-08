<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('order_items', function (Blueprint $table) {
        if (!Schema::hasColumn('order_items', 'furniture_id')) {
            $table->unsignedBigInteger('furniture_id')->nullable()->after('order_id');
        }
    });
}

public function down(): void
{
    Schema::table('order_items', function (Blueprint $table) {
        if (Schema::hasColumn('order_items', 'furniture_id')) {
            $table->dropColumn('furniture_id');
        }
    });
}
};
