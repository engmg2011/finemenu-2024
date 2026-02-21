<?php

namespace App\Http\Controllers;

use App\Constants\PermissionActions;
use App\Constants\PermissionServices;
use App\Models\User;
use App\Repository\PermissionRepositoryInterface;

class PermissionsController extends Controller
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository)
    {
    }

    public function services(){
        return response()->json(PermissionServices::getConstants());
    }

    public function actions(){
        return response()->json(PermissionActions::getConstants());
    }

    public function getUserPermissions()
    {
        $branchId = request()->route('branchId');
        $userId = request()->route('userId');
        return response()->json($this->permissionRepository->getUserPermissions($branchId, $userId));
    }

    public function setUserPermissions()
    {
        $branchId = request()->route('branchId');
        $userId = request()->route('userId');
        $permissions= request('permissions');

        return response()->json($this->permissionRepository->setUserPermissions($branchId, $userId, $permissions));
    }
}
