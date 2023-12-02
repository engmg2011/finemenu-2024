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
        Role::create(['name' => RolesConstants::ADMIN]);
        Role::create(['name' => RolesConstants::OWNER]);
        Role::create(['name' => RolesConstants::CASHIER]);
        Role::create(['name' => RolesConstants::KITCHEN]);
        Role::create(['name' => RolesConstants::SUPERVISOR]);
        Role::create(['name' => RolesConstants::GUEST]);
        Role::create(['name' => RolesConstants::DRIVER]);

        // * => create , update, delete
        Permission::create(['name'=>'restaurants.*.1']);
        User::find(1)->givePermissionTo('restaurants.*.1');
//        User::find(1)->assignRole(Roles::ADMIN);


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
