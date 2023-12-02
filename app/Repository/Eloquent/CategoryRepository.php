<?php

namespace App\Repository\Eloquent;


use App\Models\Category;
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
        return $this->model::with(['locales','media'])->orderBy('sort', 'asc')->paginate(request('per-page', 15));
    }
}
