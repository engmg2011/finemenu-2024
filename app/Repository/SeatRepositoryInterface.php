<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface SeatRepositoryInterface
{
    public function process($areaId, array $data);

    public function createModel($areaId, array $data): Model;

    public function updateModel($areaId, $id, array $data): Model;

    public function sort($areaId, $data);

    public function get($areaId, int $id);

    public function destroy($areaId, $id): ?bool;
}
