<?php

namespace App\Repository;

interface PermissionRepositoryInterface
{

    public function setPermission($userId, $businessName, $role, $businessId );


}
