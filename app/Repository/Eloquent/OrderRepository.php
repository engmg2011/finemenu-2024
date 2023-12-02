<?php

namespace App\Repository\Eloquent;


use App\Models\Order;
use App\Repository\OrderLineRepositoryInterface;

class OrderRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Order $model
     */
    public function __construct(Order $model) {
        parent::__construct($model);
    }

}
