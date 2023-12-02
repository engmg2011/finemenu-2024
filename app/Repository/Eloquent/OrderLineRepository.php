<?php

namespace App\Repository\Eloquent;



use App\Models\OrderLine;
use App\Repository\OrderLineRepositoryInterface;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param OrderLine $model
     */
    public function __construct(OrderLine $model) {
        parent::__construct($model);
    }

}
