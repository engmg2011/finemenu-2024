<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocalizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('localization', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->char("name")->nullable();
            $table->text('description')->nullable();
            $table->char("locale");
            $table->char("localizable_type");
            $table->unsignedInteger("localizable_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('localization');
    }
}
