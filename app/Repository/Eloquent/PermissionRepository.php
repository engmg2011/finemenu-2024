<?php

namespace App\Repository\Eloquent;


use App\Constants\PermissionsConstants;
use App\Models\User;
use App\Repository\PermissionRepositoryInterface;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{

    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    /**
     * @param $userId : id for the user
     * @param $businessName : like PermissionsConstants::Business
     * @param $role : like RolesConstants::OWNER
     * @param $businessId : int
     */
    public function setPermission($userId, $businessName, $role, $businessId)
    {
        $user = User::find($userId);
        $permissionName = $this->getPermissionName($businessName, $role, $businessId);
        $myWebPermission = Permission::findOrCreate($permissionName, 'web');
        $user->givePermissionTo([$myWebPermission]);
    }

    /**
     * @param $businessName : like PermissionsConstants::Business
     * @param $role : like RolesConstants::OWNER
     * @param $id : int
     * @return string
     */
    public function getPermissionName($businessName, $role, $id): string
    {
        return $businessName . '.' . $role . '.' . $id;
    }

    public function createBranchPermission($branchId, $assignUser = null)
    {
        $permissionName = PermissionsConstants::Branch.'.' . $branchId;
        Permission::findOrCreate($permissionName, 'web');
        if ($assignUser)
            $assignUser->givePermissionTo($permissionName);
    }

    public function createBusinessPermission($branchId, $assignUser = null)
    {
        $permissionName = PermissionsConstants::Business.'.' . $branchId;
        Permission::findOrCreate($permissionName, 'web');
        if ($assignUser)
            $assignUser->givePermissionTo($permissionName);
    }

}
