<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface HolidayRepositoryInterface
{
    /**
     * @return mixed
     */
    public function listModel($businessId);

    public function process($businessId, array $data);

    public function createModel($businessId, array $data): Model;

    public function updateModel($businessId, $id, array $data): Model;

    public function get($businessId, int $id);

    public function destroy($businessId, $id): ?bool;

}
