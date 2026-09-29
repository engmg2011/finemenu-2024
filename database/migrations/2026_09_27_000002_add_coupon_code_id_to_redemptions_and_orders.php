<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records which exact code was used, not only which coupon.
 * Backfilled from the single code every pre-existing coupon has.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_code_id')->nullable()->after('coupon_id');
            $table->foreign('coupon_code_id')->references('id')->on('coupon_codes')->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_code_id')->nullable()->after('coupon_id');
            $table->foreign('coupon_code_id')->references('id')->on('coupon_codes')->onDelete('set null');
        });

        DB::statement('
            UPDATE coupon_redemptions
            SET coupon_code_id = (SELECT cc.id FROM coupon_codes cc WHERE cc.coupon_id = coupon_redemptions.coupon_id LIMIT 1)
        ');

        DB::statement('
            UPDATE orders
            SET coupon_code_id = (SELECT cc.id FROM coupon_codes cc WHERE cc.coupon_id = orders.coupon_id LIMIT 1)
            WHERE coupon_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_code_id']);
            $table->dropColumn('coupon_code_id');
        });

        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropForeign(['coupon_code_id']);
            $table->dropColumn('coupon_code_id');
        });
    }
};
