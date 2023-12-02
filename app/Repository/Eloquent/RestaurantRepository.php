<?php

namespace App\Repository\Eloquent;



use App\Models\Restaurant;
use App\Repository\RestaurantRepositoryInterface;

class RestaurantRepository extends BaseRepository implements RestaurantRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Restaurant $model
     */
    public function __construct(Restaurant $model) {
        parent::__construct($model);
    }

}
