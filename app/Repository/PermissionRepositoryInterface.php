<?php

namespace App\Repository;

interface PermissionRepositoryInterface
{

    public function setPermission($userId, $businessName, $role, $businessId );
    public function createBranchPermission($branchId, $assignUser = null);

    public function getUserPermissions($branchId, $userId);
    public function setUserPermissions($branchId, $userId, $permissions);
}
