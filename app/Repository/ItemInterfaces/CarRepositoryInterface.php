<?php

namespace App\Repository\ItemInterfaces;

use Illuminate\Database\Eloquent\Model;

interface CarRepositoryInterface
{

    public function process(array $data);

    public function createModel(array $data): Model;

    public function updateModel($id, array $data): Model;

    public function set(array $data): Model;

}
