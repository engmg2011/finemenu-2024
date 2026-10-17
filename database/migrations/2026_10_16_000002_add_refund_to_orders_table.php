<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors of the order's debit invoices (see Order::syncRefundFromInvoices):
     * refund_amount   = what should be returned to the customer (chalets: insurance)
     * refunded_amount = what was actually returned (paid debit invoices)
     * refund_status   = none | partial | full
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('refund_amount', 10, 3)->default(0)->after('discount_amount');
            $table->decimal('refunded_amount', 10, 3)->default(0)->after('refund_amount');
            $table->string('refund_status', 20)->default('none')->after('refunded_amount');
        });

        // Backfill from existing debit invoices (canceled ones ignored)
        DB::statement("
            UPDATE orders o
            JOIN (
                SELECT order_id,
                       SUM(amount) AS refund_amount,
                       SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) AS refunded_amount,
                       COUNT(*) AS debits,
                       SUM(status = 'paid') AS paid
                FROM invoices
                WHERE type = 'debit' AND status <> 'canceled' AND order_id IS NOT NULL
                GROUP BY order_id
            ) d ON d.order_id = o.id
            SET o.refund_amount = d.refund_amount,
                o.refunded_amount = d.refunded_amount,
                o.refund_status = CASE
                    WHEN d.paid = 0 THEN 'none'
                    WHEN d.paid = d.debits THEN 'full'
                    ELSE 'partial'
                END
        ");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['refund_amount', 'refunded_amount', 'refund_status']);
        });
    }
};
