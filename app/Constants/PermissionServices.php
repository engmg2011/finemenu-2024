<?php

namespace App\Constants;

class PermissionServices
{
    const Invoices = "invoices";
    const Users = "users";
    const Business = "business";
    const Menu = "menu";
    const Menus = "menus";
    const Branches = "branches";
    const Roles = "roles";
    const Holidays = "holidays";
    const Categories = "categories";
    const Items = "items";
    const Reservations = "reservations";
    const Services = "services";
    const Orders = "orders";
    const Areas = "areas";
    const Seats = "seats";
    const Settings = "settings";

    public static function getConstants(): array
    {
        $reflectionClass = new \ReflectionClass(self::class);
        return $reflectionClass->getConstants();
    }
}


