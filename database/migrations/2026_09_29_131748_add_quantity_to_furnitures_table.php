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
    Schema::table('furnitures', function (Blueprint $table) {
        if (!Schema::hasColumn('furnitures', 'quantity')) {
            $table->integer('quantity')->default(0)->after('price');
        }
    });
}

public function down(): void
{
    Schema::table('furnitures', function (Blueprint $table) {
        $table->dropColumn('quantity');
    });
}
};
