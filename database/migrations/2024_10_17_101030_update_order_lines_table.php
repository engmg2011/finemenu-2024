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
        Schema::table('order_lines', static function (Blueprint $table) {
            $table->float('total_price')->default(0);
            $table->float('subtotal_price')->default(0);
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_lines', static function (Blueprint $table) {
            $table->dropColumn('total_price');
            $table->dropColumn('subtotal_price');
            $table->dropColumn('data');
        });
    }
};
