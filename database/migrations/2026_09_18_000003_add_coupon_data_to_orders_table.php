<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->json('coupon_data')->nullable()->after('delivery_address');
            $table->unsignedBigInteger('coupon_id')->nullable()->after('coupon_data');
            $table->decimal('discount_amount', 10, 3)->default(0)->after('coupon_id');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_data', 'coupon_id', 'discount_amount']);
        });
    }
};
