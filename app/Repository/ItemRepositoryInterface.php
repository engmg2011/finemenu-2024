<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface ItemRepositoryInterface
{
    /**
     * @return mixed
     */
    public function list();

    public function listModel($businessId, $branchId, $conditions = null);

    public function process(array $data);

    public function create(array $data): Model;

    public function update($id, array $data): Model;

    public function sort($data);

    public function get(int $id);

    public function destroy($id): ?bool;

    public function listHolidays($businessId ,$itemId);

    public function syncHolidays($businessId ,$itemId);

}
