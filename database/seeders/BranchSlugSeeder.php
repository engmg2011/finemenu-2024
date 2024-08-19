<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Business;
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

        foreach (Business::with('branches')->get() as &$business) {
            $b = 0;
            foreach ($business->branches as &$branch) {
                $slug = $b === 0 ? $business->slug : $business->slug . '_' . $b;
                Branch::where(['id' => $branch->id])->update(['slug' => $slug]);
                $b++;
            }

        }
    }
}
