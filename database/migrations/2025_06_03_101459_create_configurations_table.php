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
        Schema::create('configurations', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation
            $table->morphs('configurable'); // adds `configurable_id` and `configurable_type`

            // Key-value storage
            $table->string('key');
            $table->text('value')->nullable(); // use text to allow long values

            $table->timestamps();

            $table->unique(['configurable_id', 'configurable_type', 'key']); // optional unique constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configurations');
    }
};
