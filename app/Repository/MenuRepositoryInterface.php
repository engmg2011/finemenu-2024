<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface MenuRepositoryInterface
{
    /**
     * @return mixed
     */
    public function listModel($restaurantId);

    public function process($restaurantId, array $data);

    public function createModel($restaurantId, array $data): Model;

    public function updateModel($restaurantId, $id, array $data): Model;

    public function sort($restaurantId, $data);

    public function get($restaurantId, int $id);

    public function destroy($restaurantId, $id): ?bool;

    public function createMenuId(string $businessName, string|null $email): string;
}
