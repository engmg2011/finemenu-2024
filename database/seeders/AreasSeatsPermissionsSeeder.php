<?php

namespace Database\Seeders;

use App\Constants\PermissionActions;
use App\Constants\PermissionServices;
use App\Models\Branch;
use App\Repository\PermissionRepositoryInterface;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AreasSeatsPermissionsSeeder extends Seeder
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository)
    {
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Branch::all() as $branch) {
            $this->createBranchServicePermissions($branch->id);
        }
    }

    public function createBranchServicePermissions($branchId)
    {
        $permissionsToCreate = [];
        foreach ([PermissionServices::Areas, PermissionServices::Seats] as $service) {
            foreach (PermissionActions::getConstants() as $action) {
                $permissionName = 'branch.' . $branchId . '.' . $service . '.' . $action;
                $permissionsToCreate[] = [
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        if (!empty($permissionsToCreate)) {
            Permission::insert($permissionsToCreate);
        }
    }
}
