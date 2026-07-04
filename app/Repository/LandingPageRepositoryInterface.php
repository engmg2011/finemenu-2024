<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface LandingPageRepositoryInterface
{
    public function listModel($businessId);

    public function createModel($businessId, array $data): Model;

    public function updateModel($businessId, $id, array $data): Model;

    public function get($businessId, int $id);

    public function getByKey($businessId, string $key);

    public function destroy($businessId, $id): ?bool;

    public function sort($businessId, array $data);
}
