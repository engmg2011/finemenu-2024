<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Splits "what the customer types" (coupon_codes) from "the discount rule" (coupons).
 * A coupon now owns one or more codes. Existing coupons get exactly one code each,
 * copied from coupons.code (normalized to uppercase).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Codes are matched case-insensitively from now on (stored uppercase).
        // Abort if two existing codes differ only by case — they must be fixed by hand first.
        $duplicates = DB::table('coupons')
            ->selectRaw('UPPER(TRIM(code)) AS normalized, COUNT(*) AS total')
            ->groupBy('normalized')
            ->having('total', '>', 1)
            ->pluck('normalized');

        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException(
                'Cannot migrate: these coupon codes collide when uppercased: ' . $duplicates->implode(', ')
            );
        }

        Schema::create('coupon_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('coupon_id');
            $table->string('code', 64)->unique();
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->timestamps();

            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            $table->index('coupon_id');
        });

        DB::statement('
            INSERT INTO coupon_codes (coupon_id, code, redeemed_count, created_at, updated_at)
            SELECT id, UPPER(TRIM(code)), redeemed_count, created_at, updated_at FROM coupons
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_codes');
    }
};
