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

        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->enum('type', ['string', 'number', 'boolean', 'json', 'array', 'object', 'date']);
            $table->string('itemable_type')->nullable();
        });

        Schema::create('featureables', function (Blueprint $table) {
            $table->json('value');
            $table->char('value_unit', 10)->nullable();
            $table->foreignId('feature_id')->constrained('features')->onDelete('cascade');
            $table->morphs('featureable');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featureables_feature');
        Schema::dropIfExists('featureables');
        Schema::dropIfExists('features');
    }
};
