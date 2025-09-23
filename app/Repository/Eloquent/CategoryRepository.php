<?php

namespace App\Repository\Eloquent;


use App\Models\Category;
use App\Models\Menu;
use App\Repository\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Category $model
     */
    public function __construct(Category $model) {
        parent::__construct($model);
    }

    public function list()
    {
        $businessId = request()->route('businessId');;
        $menuIds = Menu::where('business_id',$businessId)->pluck('id')->toArray();
        return $this->model::whereIn('menu_id',$menuIds)->with(['locales','media'])->orderBy('sort', 'asc')->paginate(request('per-page', 15));
    }
}
