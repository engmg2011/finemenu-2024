<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface PlanRepositoryInterface
{
    public function getModel(int $id);
    public function createModel(array $data): Model;
    public function updateModel($id, array $data): Model;
    public function list();
}
