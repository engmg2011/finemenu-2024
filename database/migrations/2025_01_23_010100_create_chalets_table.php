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
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('itemable_id')->nullable();
            $table->string('itemable_type')->nullable();
        });

        Schema::create('chalets', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('insurance')->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->json('address')->nullable();  // [{'en':''},{'ar':''}
            $table->enum('frontage', ['sea_view', 'second_row'])->nullable();
            $table->integer("bedrooms")->nullable();
            $table->unsignedInteger('amount')->default(1);

            // relations
            $table->unsignedBigInteger("item_id");
            $table->foreign("item_id")->references("id")->on('items')->onDelete("cascade");
            $table->unsignedBigInteger("owner_id")->nullable();
            $table->foreign("owner_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chalets');

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('type')->nullable();
        });
    }
};
