<?php

namespace App\Repository\Eloquent;


use App\Models\Discount;
use App\Repository\DiscountRepositoryInteface;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInteface
{
    /**
     * UserRepository constructor.
     * @param Discount $model
     */
    public function __construct(Discount $model)
    {
        parent::__construct($model);
    }

}
