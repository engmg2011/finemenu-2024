<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign("package_id")->references("id")->on('packages')->onDelete("SET NULL");
            $table->unsignedBigInteger("creator_id")->nullable();
            $table->foreign("creator_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete("SET NULL");
            $table->enum('status',['pending','paid'])->default('pending');
            $table->dateTime('from')->nullable();
            $table->dateTime('to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
}
