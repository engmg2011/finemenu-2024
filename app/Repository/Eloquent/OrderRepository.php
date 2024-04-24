<?php

namespace App\Repository\Eloquent;


use App\Models\Order;
use App\Repository\OrderLineRepositoryInterface;

class OrderRepository extends BaseRepository implements OrderLineRepositoryInterface
{

    public const Relations = ['prices.locales' ,'locales' , 'orderLines.locales' , 'orderLines.prices.locales'];
    /**
     * UserRepository constructor.
     * @param Order $model
     */
    public function __construct(Order $model) {
        parent::__construct($model);
    }

    public function get($id){
        return $this->model->with(OrderRepository::Relations)->find($id);
    }

}
