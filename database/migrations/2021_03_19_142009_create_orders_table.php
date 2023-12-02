<?php

use App\Constants\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->text("note")->nullable();
            $table->timestamp("scheduled_at")->nullable();
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on('users')->onDelete("cascade");
            $table->char("orderable_type");
            $table->unsignedInteger("orderable_id");
            $table->enum("status", [
                OrderStatus::Pending,
                OrderStatus::Accepted,
                OrderStatus::Rejected,
                OrderStatus::Ready,
                OrderStatus::Delivered,
                OrderStatus::Cancelled
            ])->nullable()->default(OrderStatus::Pending);
            $table->boolean('paid')->default(false);
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
        Schema::dropIfExists('orders');
    }
}
