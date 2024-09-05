<?php

namespace Database\Seeders;

use App\Constants\RolesConstants;
use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Admin : manage all the system data "dashboard - apps"
         * BUSINESS_OWNER : manage all business data "dashboard - apps"
         * BRANCH_MANAGER : manage branch data "dashboard - apps"
         * SUPERVISOR : manage orders data "apps"
         * Customer can
         * - Manage his profile data
         * - Make orders
         * - See his orders history
         *
         *  Create all roles and permissions for every new business and branches
         *  If we will apply custom permission for like "orders apis"
         *      in branch 1 it should be "branch.1.orders"  this could be applied in /orders routes
         * - BusinessOwner (Role)
         * - business.{id}  , branch.{id} (Permission)
         * - BranchManager
         * - branch.{id}
         * - SuperVisor
         * - branch.{id}
         * - Chief
         * - branch.{id}
         * - Driver
         * - branch.{id}
         * - Cashier
         * - branch.{id} */

        Role::findOrCreate(RolesConstants::ADMIN);
        Role::findOrCreate(RolesConstants::BUSINESS_OWNER);
        Role::findOrCreate(RolesConstants::BRANCH_MANAGER);
        Role::findOrCreate(RolesConstants::SUPERVISOR);
        Role::findOrCreate(RolesConstants::CASHIER);
        Role::findOrCreate(RolesConstants::KITCHEN);
        Role::findOrCreate(RolesConstants::DRIVER);
        Role::findOrCreate(RolesConstants::GUEST);
        Role::findOrCreate(RolesConstants::CUSTOMER);

        foreach (Business::all() as $business) {
            $businessPermission = Permission::findOrCreate('business.' . $business->id);
            $owner =  User::find($business->user_id);
            $owner->assignRole(RolesConstants::BUSINESS_OWNER);
            $owner->givePermissionTo($businessPermission);

            foreach ($business->branches as $branch) {
                $permission = Permission::findOrCreate('branch.' . $branch->id);
                $owner->givePermissionTo($permission);
            }
        }


    }
}
