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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("set null");
            $table->unsignedBigInteger("menu_id")->nullable();
            $table->foreign("menu_id")->references("id")->on('menus')->onDelete("set null");
            $table->unsignedInteger('sort')->default(0);
            $table->string('slug')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
