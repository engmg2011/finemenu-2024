<?php

namespace App\Repository\ItemableInterfaces;

use Illuminate\Database\Eloquent\Model;

interface CarProductRepositoryInterface
{

    public function process(array $data);

    public function createModel(array $data): Model;

    public function updateModel($id, array $data): Model;

    public function set(array $data): Model;

}
