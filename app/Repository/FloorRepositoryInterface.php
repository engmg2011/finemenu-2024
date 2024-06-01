<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface FloorRepositoryInterface
{
    public function process($restaurantId, $branchId, array $data);

    public function createModel($restaurantId, $branchId, array $data): Model;

    public function updateModel($restaurantId, $branchId, $id, array $data): Model;

    public function sort($restaurantId, $branchId, $data);

    public function get($restaurantId, $branchId, int $id);

    public function destroy($restaurantId, $branchId, $id): ?bool;
}
