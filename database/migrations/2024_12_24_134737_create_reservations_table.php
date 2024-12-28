<?php

use App\Constants\PaymentConstants;
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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('from');
            $table->dateTime('to');
            $table->unsignedBigInteger('reservable_id')->nullable();
            $table->string('reservable_type')->nullable();
            $table->json('data')->nullable();
            $table->enum('status', [PaymentConstants::RESERVATION_PENDING,PaymentConstants::RESERVATION_COMPLETED,
                PaymentConstants::RESERVATION_CANCELED, PaymentConstants::RESERVATION_REFUNDED])
                ->default(PaymentConstants::INVOICE_PENDING);
            $table->unsignedBigInteger("order_id")->nullable();
            $table->foreign("order_id")->references("id")->on('orders')->onDelete("SET NULL");
            $table->unsignedBigInteger("order_line_id")->nullable();
            $table->foreign("order_line_id")->references("id")->on('order_lines')->onDelete("SET NULL");
            $table->unsignedBigInteger("reserved_by_id")->nullable();
            $table->foreign("reserved_by_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("reserved_for_id")->nullable();
            $table->foreign("reserved_for_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("SET NULL");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("SET NULL");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
