<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('discount_type', ['fixed', 'percentage']);
            $table->decimal('discount_value', 10, 3);
            $table->enum('usage_type', ['single', 'multi']);
            $table->unsignedInteger('usage_limit')->nullable()->comment('null = unlimited for multi-use');
            $table->unsignedInteger('redeemed_count')->default(0);
            $table->boolean('is_active')->default(true);

            // When the coupon itself may be used (today must fall within this window)
            $table->date('usage_start_date');
            $table->date('usage_end_date');

            // Optional: the reservation window this coupon is valid for.
            // When set, the order's reservation dates must fall within these dates.
            $table->date('reservation_start_date')->nullable();
            $table->date('reservation_end_date')->nullable();

            $table->unsignedBigInteger('business_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->timestamps();

            $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
