<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface BranchRepositoryInterface
{

    public function process(array $data);

    public function createModel($restaurantId, array $data): Model;

    public function updateModel($restaurantId, $id, array $data): Model;

    public function sort($restaurantId, $data);

    public function get($restaurantId, int $id);

    public function destroy($restaurantId, $id): ?bool;
}
