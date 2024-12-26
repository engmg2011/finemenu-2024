<?php


namespace App\Repository;

interface ReservationRepositoryInterface
{

    public function process(array $data): array;

    public function get($id);

    public function create(array $data);

    public function update($id, array $data);

    public function list($conditions = null);


}
