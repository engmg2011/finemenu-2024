<?php

namespace App\Actions;

use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class PermissionAction
{

    public function setPermission($userId, $permissionName)
    {
        $user = User::find($userId);
        Permission::findOrCreate($permissionName);
        $user->givePermissionTo([$permissionName]);
    }


    public function setHotelOwnerPermissions($ownerId, $hotelId)
    {
        $permissionName = PermissionsConstants::Hotels.'.'.RolesConstants::OWNER.'.' . $hotelId;
        $this->setPermission($ownerId, $permissionName);
    }

    public function setRestaurantOwnerPermissions($ownerId, $restaurantId)
    {
        $permissionName = PermissionsConstants::Restaurants.'.'.RolesConstants::OWNER.'.' . $restaurantId;
        $this->setPermission($ownerId, $permissionName);
    }

    public function setRestaurantSupervisorPermissions($ownerId, $restaurantId)
    {
        $permissionName = PermissionsConstants::Restaurants.'.'.RolesConstants::SUPERVISOR.'.' . $restaurantId;
        $this->setPermission($ownerId, $permissionName);
    }

    public function setKitchenUserPermissions($userId, $restaurantId)
    {
        $permissionName = PermissionsConstants::Restaurants.'.'.RolesConstants::KITCHEN.'.' . $restaurantId;
        $this->setPermission($userId, $permissionName);
    }

    public function setCashierPermissions($userId, $restaurantId)
    {
        $permissionName = PermissionsConstants::Restaurants.'.'.RolesConstants::CASHIER.'.' . $restaurantId;
        $this->setPermission($userId, $permissionName);
    }

    public function setDriverPermissions($userId, $restaurantId)
    {
        $permissionName = PermissionsConstants::Restaurants.'.'.RolesConstants::DRIVER.'.' . $restaurantId;
        $this->setPermission($userId, $permissionName);
    }

}
