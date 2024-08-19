<?php

namespace Database\Seeders;

use App\Models\Business;
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

        foreach (Business::with('user')->get() as &$business) {
            $menuSlug = $this->menuRepository->createMenuId($business->name, $business->user->email);
            if(!$menuSlug)
                throw new \Exception("error restID ". $business->id);
            $data = [
                'slug' => $menuSlug,
                'business_id' => $business->id,
                'user_id' => $business->user_id,
                'locales' => [['name' => $business->name, 'locale' => 'en']]
            ];
            $menu = $this->menuRepository->createModel($data);
            $data['menu_id'] = $menu->id;
            $this->branchRepository->createModel($data);
        }
    }
}
