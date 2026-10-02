<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A redemption is now created as a temporary "pending" hold when the order is placed,
 * and becomes "confirmed" (counted as used) only when the order is paid.
 * Existing rows were recorded under the old flow and are treated as confirmed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed'])->default('confirmed')->after('order_id');
            $table->timestamp('expires_at')->nullable()->after('status')
                ->comment('Pending holds stop reserving the code after this time');
            $table->index(['coupon_code_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('coupon_redemptions', function (Blueprint $table) {
            $table->dropIndex(['coupon_code_id', 'status', 'expires_at']);
            $table->dropColumn(['status', 'expires_at']);
        });
    }
};
