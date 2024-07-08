<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Repository\BranchRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Database\Seeder;

// sail artisan db:seed --class=CategoriesMenuIdSeeder;
class CategoriesMenuIdSeeder extends Seeder
{
    public function __construct(private readonly MenuRepositoryInterface            $menuRepository,
                                private readonly BranchRepositoryInterface $branchRepository)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        foreach (Restaurant::with('user')->get() as &$restaurant) {
            $menuSlug = $this->menuRepository->createMenuId($restaurant->name, $restaurant->user->email);
            if(!$menuSlug)
                throw new \Exception("error restID ". $restaurant->id);
            $data = [
                'slug' => $menuSlug,
                'restaurant_id' => $restaurant->id,
                'user_id' => $restaurant->user_id,
                'locales' => [['name' => $restaurant->name, 'locale' => 'en']]
            ];
            $menu = $this->menuRepository->createModel($data);
            $data['menu_id'] = $menu->id;
            $this->branchRepository->createModel($data);
        }
    }
}
