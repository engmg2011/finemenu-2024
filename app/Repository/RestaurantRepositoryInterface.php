<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface RestaurantRepositoryInterface
{
    public function processRestaurant(array $data): array;

    public function createModel(array $data): Model;

    public function setModel(&$model, &$data);

    public function updateModel($id, array $data): Model;

    public function list();

    public function getModel(int $id);

    public function menu($restaurantId);

    public function dietMenu($restaurantId): array;
}
