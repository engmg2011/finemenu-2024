<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->enum('type', ['string', 'number', 'boolean', 'json', 'array', 'object', 'date'])->change();
            $table->string('icon')->nullable();
            $table->string('icon-font-type')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->dropColumn('icon-font-type');
            $table->dropColumn('color');
            $table->dropColumn('sort');
        });
    }
};
