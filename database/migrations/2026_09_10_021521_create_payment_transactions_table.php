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
    Schema::create('payment_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->string('gateway'); // Phân biệt các cổng thanh toán (Momo, VNPay, COD, v.v.)
        $table->string('gateway_order_id')->nullable()->index(); // ID đơn hàng từ cổng thanh toán
        $table->string('transaction_id')->nullable()->index(); // ID giao dịch từ cổng thanh toán
        $table->decimal('amount', 15, 2); // Số tiền thanh toán
        $table->string('status')->default('pending'); // Trạng thái: pending, completed, failed, canceled
        $table->integer('result_code')->nullable(); // Mã kết quả từ cổng thanh toán
        $table->string('message')->nullable(); // Thông điệp/thông báo lỗi từ cổng thanh toán
        $table->json('request_payload')->nullable(); // Dữ liệu gửi đi
        $table->json('response_payload')->nullable(); // Dữ liệu phản hồi về
        $table->timestamp('paid_at')->nullable(); // Thời gian thanh toán thành công
        $table->timestamps();

        $table->unique(['gateway', 'gateway_order_id']);
        $table->index(['order_id', 'status']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
