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

        Schema::rename('floors', 'areas');
        Schema::rename('tables', 'seats');
        Schema::table('seats', function (Blueprint $table){
            $table->dropForeign(['floor_id']);
            $table->renameColumn('floor_id', 'area_id');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('areas', 'floors');
        Schema::table('seats', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->renameColumn('area_id', 'floor_id');
        });
        Schema::rename('seats', 'tables');
        Schema::table('tables', function (Blueprint $table) {
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('cascade');
        });
    }
};
