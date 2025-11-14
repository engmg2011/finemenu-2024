<?php

use App\Constants\CategoryTypes;
use App\Models\Category;
use App\Models\Menu;
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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('type')->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->string("icon")->nullable();
            $table->enum('type' , [
                CategoryTypes::PRODUCT,
                CategoryTypes::SERVICE,
                CategoryTypes::FEATURES
            ])->nullable()->default(CategoryTypes::PRODUCT)->change();
            $table->string('itemable_type')->nullable();
            $table->unsignedBigInteger("business_id")->nullable();
            $table->foreign("business_id")->references("id")->on('business')->onDelete("cascade");
        });
        Menu::all()->each(function ($menu) {
           $business_id = $menu->business_id;
           $user_id = $menu->user_id;
           Category::where('menu_id', $menu->id)->update(['business_id' => $business_id , 'user_id' => $user_id]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('itemable_type');
        });
    }
};
