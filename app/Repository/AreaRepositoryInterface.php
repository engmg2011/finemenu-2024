<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface AreaRepositoryInterface
{
    public function process($businessId, $branchId, array $data);

    public function createModel($businessId, $branchId, array $data): Model;

    public function updateModel($businessId, $branchId, $id, array $data): Model;

    public function sort($businessId, $branchId, $data);

    public function get($businessId, $branchId, int $id);

    public function destroy($businessId, $branchId, $id): ?bool;
}
