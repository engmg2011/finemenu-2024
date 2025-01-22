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
            $table->boolean('hide')->default(false);
            $table->boolean('disable_ordering')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        schema::table('items', function (Blueprint $table) {
            $table->dropColumn('hide');
            $table->dropColumn('disable_ordering');
        });
    }
};
