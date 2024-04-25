<?php


namespace App\Repository;

interface OrderRepositoryInterface
{

    public function get($id);

    public function process(array $data): array;

    public function create(array $data);

    public function setOrderData(&$model, &$data);

    public function update($id, array $data);

    public function list($conditions = null);

    public function kitchenOrders($restaurantId = null);

    public function cashierOrders();

    public function driverOrders();

    public function getOrderPermittedRules(&$order): array;

}
