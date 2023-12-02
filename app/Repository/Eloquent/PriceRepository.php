<?php

namespace App\Repository\Eloquent;



use App\Models\Price;
use App\Repository\PriceRepositoryInterface;

class PriceRepository extends BaseRepository implements PriceRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Price $model
     */
    public function __construct(Price $model) {
        parent::__construct($model);
    }

}
