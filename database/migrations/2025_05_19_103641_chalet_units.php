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
        Schema::table('chalets', function (Blueprint $table) {
            $table->dropColumn('amount');
        });
        Schema::table('chalets', function (Blueprint $table) {
            $table->unsignedInteger('units')->default(1);
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->unsignedInteger('unit')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chalets', function (Blueprint $table) {
            $table->dropColumn('units');
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('unit');
        });
    }
};
