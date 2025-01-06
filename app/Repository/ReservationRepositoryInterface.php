<?php


namespace App\Repository;

use App\Models\Item;
use App\Models\OrderLine;

interface ReservationRepositoryInterface
{

    public function process(array $data): array;

    public function get($id);

    public function create(array $data);

    public function update($id, array $data);

    public function listModel($businessId, $branchId, $conditions = null);

    public function set(Item $item, OrderLine $orderLine, array $reservation);

}
