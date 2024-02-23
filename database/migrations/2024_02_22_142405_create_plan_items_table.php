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
        Schema::create('plan_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('plan_id');
            $table->unsignedBiginteger('item_id');
            $table->foreign('plan_id')->references('id')
                ->on('plans')->onDelete('cascade');
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
        Schema::dropIfExists('plan_item');
    }
};
