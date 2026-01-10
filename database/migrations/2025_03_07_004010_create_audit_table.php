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
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string("service_type");
            $table->unsignedBigInteger('service_id');
            $table->string("message");
            $table->json("request");
            $table->json("data")->nullable();

            $table->timestamps();

            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete("cascade");

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
        Schema::dropIfExists('audits');
    }
};
