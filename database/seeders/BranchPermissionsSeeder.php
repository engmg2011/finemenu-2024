<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Repository\PermissionRepositoryInterface;
use Illuminate\Database\Seeder;

class BranchPermissionsSeeder extends Seeder
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository)
    {
    }

    /**
     * Run the database seeds.
     *   Create all permissions for every new business and branches
     *   If we will apply custom permission for like "orders apis"
     *       in branch 1 it should be "branch.1.orders"  this could be applied in /orders routes
     *
     */
    public function run(): void
    {
        foreach (Branch::all() as $branch) {
            $this->permissionRepository->createBranchServicePermissions($branch->id);
        }
    }
}
