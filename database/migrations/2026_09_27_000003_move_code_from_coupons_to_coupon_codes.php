<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * coupon_codes is now the only place codes live, so coupons.code is removed.
 * code_prefix is the group tag used when generating codes (e.g. "ZAIN20" → "ZAIN20-K7QM3XPA").
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code_prefix', 20)->nullable()->after('id');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code')->nullable()->after('id');
        });

        // Restore each coupon's oldest code; coupons with generated codes get their first one.
        DB::statement('
            UPDATE coupons
            SET code = (SELECT cc.code FROM coupon_codes cc WHERE cc.coupon_id = coupons.id ORDER BY cc.id LIMIT 1)
        ');

        Schema::table('coupons', function (Blueprint $table) {
            $table->unique('code');
            $table->dropColumn('code_prefix');
        });
    }
};
