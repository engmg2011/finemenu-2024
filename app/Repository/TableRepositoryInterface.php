<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface TableRepositoryInterface
{
    public function process($floorId, array $data);

    public function createModel($floorId, array $data): Model;

    public function updateModel($floorId, $id, array $data): Model;

    public function sort($floorId, $data);

    public function get($floorId, int $id);

    public function destroy($floorId, $id): ?bool;
}
