<?php

use App\Constants\BusinessTypes;
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
        Schema::create('business', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->char("name");
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on('users')->onDelete("cascade");
            $table->char("passcode", 255)->nullable();
            $table->char("slug", 255)->unique()->nullable();
            $table->enum('type' , [BusinessTypes::RESTAURANT, BusinessTypes::SALON, BusinessTypes::HOTEL])
                ->nullable()->default(BusinessTypes::RESTAURANT);
            $table->unsignedBigInteger("creator_id")->nullable();
            $table->foreign("creator_id")->references("id")->on('users')->onDelete("SET NULL");
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business');
    }
};
