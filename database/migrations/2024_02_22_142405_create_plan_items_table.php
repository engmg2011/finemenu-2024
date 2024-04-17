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
        Schema::create('diet_plan_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('diet_plan_id');
            $table->unsignedBiginteger('item_id');
            $table->foreign('diet_plan_id')->references('id')
                ->on('diet_plans')->onDelete('cascade');
            $table->foreign('item_id')->references('id')
                ->on('items')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_plan_item');
    }
};
