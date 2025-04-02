<?php

namespace App\Repository\Eloquent;


use App\Constants\PermissionActions;
use App\Constants\PermissionsConstants;
use App\Constants\PermissionServices;
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
        $this->createBranchServicePermissions($branchId);

    }

    public function createBranchServicePermissions($branchId)
    {
        foreach (PermissionServices::getConstants() as $service) {
            foreach(PermissionActions::getConstants() as $action) {
                Permission::findOrCreate('branch.' . $branchId .'.'.$service.'.'.$action, 'web');
            }
        }
    }

    public function createBusinessPermission($branchId, $assignUser = null)
    {
        $permissionName = PermissionsConstants::Business.'.' . $branchId;
        Permission::findOrCreate($permissionName, 'web');
        if ($assignUser)
            $assignUser->givePermissionTo($permissionName);
    }

    public function getUserPermissions($branchId, $userId)
    {
        $user = User::find($userId);
        return $user->getAllPermissions()
            ->filter(function($permission) use($branchId){
                return str_starts_with($permission->name, "branch.$branchId.");
            })
            ->pluck('name');
    }

    public function setUserPermissions($branchId, $userId, $permissions)
    {
        $user = User::find($userId);

        $actions = PermissionActions::getConstants();
        $services = PermissionServices::getConstants();

        $newPermissions = [];
        $removedPermissions = [];
        if(isset($permissions)){
            foreach ($services as $service){
                foreach ($actions as $action){
                    $prem = "branch.$branchId.$service.$action";
                    if(in_array("$service.$action", $permissions)){
                        $newPermissions[] = $prem;
                    }else{
                        $removedPermissions[] = $prem;
                    }
                }
            }
            $user->syncPermissions($newPermissions);

            $user->revokePermissionTo($removedPermissions);
        }
        return $this->getUserPermissions($branchId, $userId);
    }
}
