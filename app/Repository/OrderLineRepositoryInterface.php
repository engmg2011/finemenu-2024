<?php


namespace App\Repository;


interface OrderLineRepositoryInterface
{

    public function createManyOLs($orderId, array $data) : array;
}
