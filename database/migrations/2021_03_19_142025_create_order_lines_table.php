<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->char("note")->nullable();
            $table->unsignedBigInteger("item_id")->nullable();
            $table->foreign("item_id")->references("id")->on('items')->onDelete("set null");
            $table->unsignedBigInteger("order_id")->nullable();
            $table->foreign("order_id")->references("id")->on('orders')->onDelete("set null");
            $table->unsignedBigInteger("content_id")->nullable();
            $table->foreign("content_id")->references("id")->on('contents')->onDelete("set null");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete("set null");
            $table->integer("count")->default(1)->nullable();
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
        Schema::dropIfExists('order_lines');
    }
}
