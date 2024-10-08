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
        Schema::create('floors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("business_id");
            $table->foreign("business_id")->references("id")->on('business')->onDelete("cascade");
            $table->unsignedBigInteger("branch_id");
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("cascade");
            $table->unsignedInteger('sort')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floors');
    }
};
