<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface MenuRepositoryInterface
{
    /**
     * @return mixed
     */
    public function listModel($businessId);

    public function process($businessId, array $data);

    public function createModel($businessId, array $data): Model;

    public function updateModel($businessId, $id, array $data): Model;

    public function sort($businessId, $data);

    public function get($businessId, int $id);

    public function destroy($businessId, $id): ?bool;

    public function createMenuId(string $businessName, string|null $email): string;
}
