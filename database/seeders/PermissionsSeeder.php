<?php

namespace Database\Seeders;

use App\Constants\RolesConstants;
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
         *  Create admin role
         * Admin can manage all the data
         */
        Role::findOrCreate(RolesConstants::ADMIN);
        Role::findOrCreate(RolesConstants::OWNER);
        Role::findOrCreate(RolesConstants::CASHIER);
        Role::findOrCreate(RolesConstants::KITCHEN);
        Role::findOrCreate(RolesConstants::SUPERVISOR);
        Role::findOrCreate(RolesConstants::GUEST);
        Role::findOrCreate(RolesConstants::DRIVER);

        // * => create , update, delete
        /*Permission::findOrCreate('restaurants.*.1');
        User::find(1)->givePermissionTo('restaurants.*.1');
        User::find(1)->assignRole(Roles::ADMIN);*/


        /**
         * Create user role
         * User can manage any assigned data to him
         */

        /**
         * Create customer role
         * Customer can
         * - Manage his profile data
         * - Make orders
         * - See his orders history
         */

    }
}
