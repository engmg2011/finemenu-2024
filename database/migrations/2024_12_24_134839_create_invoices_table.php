<?php

use App\Constants\PaymentConstants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 8,3)->unsigned()->default(0);
            $table->json('data')->nullable();
            $table->string('external_link')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->enum('type', [PaymentConstants::INVOICE_CREDIT, PaymentConstants::INVOICE_DEBIT])
                ->default(PaymentConstants::INVOICE_CREDIT);

            $table->enum('status', [PaymentConstants::INVOICE_PENDING,
                PaymentConstants::INVOICE_PAID, PaymentConstants::INVOICE_CANCELED])
                ->default(PaymentConstants::INVOICE_PENDING);

            $table->dateTime('status_changed_at')->nullable();

            $table->enum('payment_type', [PaymentConstants::TYPE_CASH, PaymentConstants::TYPE_ONLINE,
                PaymentConstants::TYPE_KNET, PaymentConstants::TYPE_LINK,
                PaymentConstants::TYPE_CHECK, PaymentConstants::TYPE_WAMD,
                PaymentConstants::TYPE_TRANSFER])
                ->default(PaymentConstants::TYPE_CASH);

            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->foreign("reservation_id")->references("id")->on('reservations')->onDelete("SET NULL");

            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign("order_id")->references("id")->on('orders')->onDelete("SET NULL");

            $table->unsignedBigInteger("order_line_id")->nullable();
            $table->foreign("order_line_id")->references("id")->on('order_lines')->onDelete("SET NULL");

            $table->unsignedBigInteger("invoice_by_id")->nullable();
            $table->foreign("invoice_by_id")->references("id")->on('users')->onDelete("SET NULL");

            $table->unsignedBigInteger("invoice_for_id")->nullable();
            $table->foreign("invoice_for_id")->references("id")->on('users')->onDelete("SET NULL");

            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("SET NULL");

            $table->unsignedBigInteger("branch_id")->nullable();
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("SET NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
