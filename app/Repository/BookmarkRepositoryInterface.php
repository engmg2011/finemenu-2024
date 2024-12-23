<?php


namespace App\Repository;


interface BookmarkRepositoryInterface
{
    public function get(int $id);

    public function process(array $data): array;

    public function create(array $data);

    public function update($id, array $data);

    public function list();

    public function userBookmarks($businessId, $branchId);

    public function syncBookmarks($bookmarks, $businessId, $branchId);

}
