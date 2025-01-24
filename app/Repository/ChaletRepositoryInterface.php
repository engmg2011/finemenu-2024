<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface ChaletRepositoryInterface
{

    public function process(array $data);

    public function createModel(array $data): Model;

    public function updateModel($id, array $data): Model;

    public function set(array $data): Model;

}
