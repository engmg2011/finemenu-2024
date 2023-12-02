<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatorId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotels' , function (Blueprint $table){
            $table->unsignedBigInteger("creator_id")->nullable();
            $table->foreign("creator_id")->references("id")->on('users')->onDelete("SET NULL");
        });
        Schema::table('restaurants' , function (Blueprint $table){
            $table->unsignedBigInteger("creator_id")->nullable();
            $table->foreign("creator_id")->references("id")->on('users')->onDelete("SET NULL");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotels' , function (Blueprint $table){
            $table->dropColumn("creator_id");
        });
        Schema::table('restaurants' , function (Blueprint $table){
            $table->dropColumn("creator_id");
        });
    }
}
