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
        Schema::create('diet_plan_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diet_plan_id')->nullable();
            $table->foreign("diet_plan_id")->references("id")->on('diet_plans')->onDelete("SET NULL");
            $table->unsignedBigInteger("creator_id")->nullable();
            $table->foreign("creator_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("restaurant_id");
            $table->foreign("restaurant_id")->references("id")->on('restaurants')->onDelete("cascade");
            $table->enum('status',['pending','paid'])->default('pending');
            $table->json('selected_meals');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_subscriptions');
    }
};
