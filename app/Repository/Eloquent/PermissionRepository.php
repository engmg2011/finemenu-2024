<?php

namespace App\Repository\Eloquent;


use App\Constants\PermissionActions;
use App\Constants\PermissionsConstants;
use App\Constants\PermissionServices;
use App\Constants\RolesConstants;
use App\Models\Business;
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
        $permissionName = PermissionsConstants::Branch . '.' . $branchId;
        Permission::findOrCreate($permissionName, 'web');
        if ($assignUser)
            $assignUser->givePermissionTo($permissionName);
        $this->createBranchServicePermissions($branchId);

    }

    public function createBranchServicePermissions($branchId)
    {
        foreach (PermissionServices::getConstants() as $service) {
            foreach (PermissionActions::getConstants() as $action) {
                Permission::findOrCreate('branch.' . $branchId . '.' . $service . '.' . $action, 'web');
            }
        }
    }

    public function createBusinessPermission($branchId, $assignUser = null)
    {
        $permissionName = PermissionsConstants::Business . '.' . $branchId;
        Permission::findOrCreate($permissionName, 'web');
        if ($assignUser)
            $assignUser->givePermissionTo($permissionName);
    }

    public function getUserPermissions($branchId, $userId)
    {
        $user = User::find($userId);
        $control = $user->control;
        $dashboard_access = $user->dashboard_access;
        $permissions = $user->getAllPermissions()
            ->filter(function ($permission) use ($branchId) {
                return str_starts_with($permission->name, "branch.$branchId.");
            })
            ->pluck('name');
        return compact('permissions','control', 'dashboard_access');
    }

    /**
     * Set control data
     * "control": [{
     *      "branch_ids": [ "2" , "5"],
     *      "business_id": "2"
     * }],
     * @return void
     */
    public function setControlData($branchId,$user)
    {
            $controlData = $user->control;
            $businessId = request()->route('businessId');
            $isFound = false;
            if (is_array($controlData)) {
                foreach ($controlData as &$control) {
                    if (intval($control['business_id']) === intval($businessId)) {
                        $branchIds = $control['branch_ids'];
                        if(is_array($branchIds)){
                            $branchIds[] = $branchId;
                            $control['branch_ids'] = array_unique($branchIds);
                        }else{
                            $control['branch_ids'] = [$branchId];
                        }
                        $isFound = true;
                    }
                }
            }
            if (!$isFound) {
                $controlData[] = [
                    'business_id' => request()->route('businessId'),
                    'branch_ids' => [$branchId]
                ];
            }
            $user->update(['control' => $controlData]);
            $user->assignRole(RolesConstants::BRANCH_MANAGER);
    }

    public function setUserPermissions($branchId, $userId, $permissions)
    {
        $user = User::find($userId);
        $businessId = (int) request()->route('businessId');

        if ( auth('sanctum')->user()->email !== "eng.mg2011"."@gmail.com" &&
            auth('sanctum')->user()->id !== Business::find($businessId)->user_id) {
            abort(403, "Not permitted");
        }

        $actions = PermissionActions::getConstants();
        $services = PermissionServices::getConstants();

        $dashboardAccess = request()->get('dashboard_access', false);
        if (!$dashboardAccess) {
            // reset permissions as all services permissions will be revoked
            $permissions = [];
            $user->update(['control' => null]);
            $user->removeRole(PermissionsConstants::Branch . '.' . $branchId);
            $user->revokePermissionTo(PermissionsConstants::Branch . '.' . $branchId);
        } else {
            Permission::findOrCreate(PermissionsConstants::Branch . '.' . $branchId , 'web');
            $user->givePermissionTo(PermissionsConstants::Branch . '.' . $branchId);
            $this->setControlData($branchId,$user);
        }

        $newPermissions = [];
        $removedPermissions = [];
        foreach ($services as $service) {
            foreach ($actions as $action) {
                $prem = "branch.$branchId.$service.$action";
                if (in_array("$service.$action", $permissions)) {
                    $newPermissions[] = $prem;
                } else {
                    $removedPermissions[] = $prem;
                }
            }
        }
        $user->syncPermissions($newPermissions);
        $user->revokePermissionTo($removedPermissions);
        $user->update(['dashboard_access' => $dashboardAccess]);
        return $this->getUserPermissions($branchId, $userId);
    }
}
