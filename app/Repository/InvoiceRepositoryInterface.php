<?php


namespace App\Repository;

interface InvoiceRepositoryInterface
{

    public function get($id);

    public function process(array $data): array;

    public function create(array $data);

    public function update($id, array $data);

    public function list($conditions = null);


}
