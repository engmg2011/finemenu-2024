<?php

use App\Enums\CarColor;
use App\Enums\CarShapeType;
use App\Enums\DriveType;
use App\Enums\EngineType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', callback: function (Blueprint $table) {
            $table->id();
            $table->enum('color', [CarColor::all()])->nullable();
            $table->string('brand_id')->nullable();
            $table->foreign('brand_id')->references('id')->on('car_brands')->onDelete('cascade');
            $table->string('model_id')->nullable();
            $table->foreign('model_id')->references('id')->on('car_models')->onDelete('cascade');
            $table->string('year')->nullable();
            $table->string('vin')->nullable();
            $table->string('license_plate')->nullable();
            $table->enum('shape_type', CarShapeType::all())->default(CarShapeType::SEDAN)->nullable();
            $table->integer('mileage')->nullable();
            $table->enum('engine_type', EngineType::all())->default(EngineType::PETROL)->nullable();
            $table->enum('drive_type', DriveType::all())->default(DriveType::FWD)->nullable();
            $table->unsignedBigInteger("item_id");
            $table->foreign("item_id")->references("id")->on('items')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
