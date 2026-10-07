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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('phone');
        $table->string('address');
        $table->integer('to_district_id');
        $table->string('to_ward_code');
        $table->decimal('total_price', 15, 0);
        $table->decimal('ghn_total_fee', 15, 0)->default(0);
        $table->string('status')->default('pending');
        $table->string('shipping_status')->default('pending');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
