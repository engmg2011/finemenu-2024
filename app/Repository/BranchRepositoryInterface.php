<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface BranchRepositoryInterface
{

    public function process(array $data);

    public function createModel($businessId, array $data): Model;

    public function updateModel($businessId, $id, array $data): Model;

    public function sort($businessId, $data);

    public function get($businessId, int $id);

    public function destroy($businessId, $id): ?bool;
}
