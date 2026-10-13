<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'ghn_order_code')) {
                $table->string('ghn_order_code')->nullable()->index();
            }
            if (!Schema::hasColumn('orders', 'ghn_total_fee')) {
                $table->integer('ghn_total_fee')->default(0);
            }
            if (!Schema::hasColumn('orders', 'to_district_id')) {
                $table->integer('to_district_id')->nullable();
            }
            if (!Schema::hasColumn('orders', 'to_ward_code')) {
                $table->string('to_ward_code')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ghn_order_code', 'ghn_total_fee', 'to_district_id', 'to_ward_code']);
        });
    }
};