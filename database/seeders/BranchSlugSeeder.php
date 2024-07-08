<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

// sail artisan db:seed --class=CategoriesMenuIdSeeder;
class BranchSlugSeeder extends Seeder
{
    public function __construct()
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (Restaurant::with('branches')->get() as &$restaurant) {
            $b = 0;
            foreach ($restaurant->branches as &$branch) {
                $slug = $b === 0 ? $restaurant->slug : $restaurant->slug . '_' . $b;
                Branch::where(['id' => $branch->id])->update(['slug' => $slug]);
                $b++;
            }

        }
    }
}
