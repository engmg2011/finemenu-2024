<?php

namespace App\Repository;

interface PermissionRepositoryInterface
{

    public function setPermission($userId, $permissionName);

    public function setHotelOwnerPermissions($ownerId, $hotelId);

    public function setRestaurantOwnerPermissions($ownerId, $restaurantId);

    public function setRestaurantSupervisorPermissions($ownerId, $restaurantId);

    public function setKitchenUserPermissions($userId, $restaurantId);

    public function setCashierPermissions($userId, $restaurantId);

    public function setDriverPermissions($userId, $restaurantId);
}
