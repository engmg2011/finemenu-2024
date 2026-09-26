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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->timestamps();
            $table->unsignedBigInteger("user_id");
            $table->foreign("user_id")->references("id")->on('users')->onDelete("cascade");
            $table->integer('sort')->nullable();
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("SET NULL");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->foreign("branch_id")->references("id")->on('branches')->onDelete("SET NULL");
        });

        DB::unprepared('
            CREATE TRIGGER before_insert_pages
            BEFORE INSERT ON pages
            FOR EACH ROW
            BEGIN
                IF NEW.sort IS NULL OR NEW.sort = 0 THEN
                    SET NEW.sort = (
                        SELECT COALESCE(MAX(sort), 0) + 1
                        FROM pages
                    );
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS before_insert_pages');
        Schema::dropIfExists('pages');
    }
};
