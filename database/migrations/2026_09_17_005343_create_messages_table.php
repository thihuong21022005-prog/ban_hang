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
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        // Người gửi
        $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
        // Người nhận
        $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
        // Nội dung
        $table->text('content');
        // Trạng thái (đã đọc hay chưa)
        $table->boolean('is_read')->default(false);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
