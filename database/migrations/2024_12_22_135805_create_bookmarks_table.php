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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("item_id");
            $table->foreign("item_id")->references("id")->on('items')->onDelete("cascade");
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on('users')->onDelete("cascade");
            $table->unsignedBigInteger("branch_id");
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("cascade");
            $table->unsignedBigInteger("business_id");
            $table->foreign("business_id")->references("id")->on('business')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
