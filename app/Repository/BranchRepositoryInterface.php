<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;

interface BranchRepositoryInterface
{
    /**
     * @return mixed
     */
    public function list();

    public function process(array $data);

    public function createModel(array $data): Model;

    public function update($id, array $data): Model;

    public function sort($data);

    public function get(int $id);

    public function destroy($id): ?bool;
}
