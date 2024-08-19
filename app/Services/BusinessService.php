<?php

namespace App\Services;

use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use App\Repository\BranchRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Repository\PermissionRepositoryInterface;

readonly class BusinessService
{
    public function __construct(
        private PermissionRepositoryInterface $permissionRepository,
        private BranchRepositoryInterface     $branchRepository,
        private MenuRepositoryInterface       $menuRepository)
    {
    }


    public function registerationBusinessData($request, $user )
    {
        $menuSlug = $this->menuRepository->createMenuId($request->businessName, $user->email);
        return [
            'user_id' => $user->id,
            'creator_id' => $user->id,
            'name' => $request->businessName,
            'email' => $request->email,
            'slug' => $menuSlug,
            "locales" => [["name" => $request->businessName, "locale" => "en"]]
        ];
    }

    public function createBusiness($model, $data)
    {
        // create menu
        $data['business_id'] = $model->id;
        $menu = $this->menuRepository->createModel($model->id, $data);

        // create branch
        $data['menu_id'] = $menu->id;
        $this->branchRepository->createModel($model->id, $data);

        // give owner permissions
        $userId = $data['user_id'] ?? auth('api')->id();
        $this->permissionRepository->setPermission(
            $userId,
            PermissionsConstants::Business,
            RolesConstants::OWNER,
            $model->id);
    }
}
