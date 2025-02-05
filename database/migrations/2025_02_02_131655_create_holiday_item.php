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
        Schema::create('holiday_item', function (Blueprint $table) {
            $table->id();
            $table->decimal('price', 8,3)->unsigned()->default(0);
            $table->unsignedBigInteger("item_id");
            $table->foreign("item_id")->references("id")->on('items')->onDelete("cascade");
            $table->unsignedBigInteger("holiday_id");
            $table->foreign("holiday_id")->references("id")->on('holidays')->onDelete("cascade");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("SET NULL");
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("SET NULL");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_item');
    }
};
