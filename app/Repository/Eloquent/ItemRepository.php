<?php

namespace App\Repository\Eloquent;


use App\Models\Item;
use App\Repository\ItemRepositoryInterface;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Item $model
     */
    public function __construct(Item $model)
    {
        parent::__construct($model);
    }

    public function list()
    {
        return $this->model::with(['locales', 'media', 'prices', 'addons', 'discounts'])
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

}
