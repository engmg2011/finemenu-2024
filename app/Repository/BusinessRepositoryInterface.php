<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface BusinessRepositoryInterface
{
    public function process(&$data): array;

    public function createModel(array $data): Model;

    public function setModelRelations(&$model, &$data);

    public function updateModel($id, array $data): Model;

    public function list();

    public function getModel(int $id);

    public function dietMenu($restaurantId): array;
}
