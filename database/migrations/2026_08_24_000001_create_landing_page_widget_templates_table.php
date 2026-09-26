<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('landing_page_widget_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_type');
            $table->string('key');
            $table->string('type');
            $table->boolean('active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->json('fields')->nullable();
            $table->json('data')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->index(['business_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('landing_page_widget_templates');
    }
};
