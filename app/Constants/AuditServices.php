<?php

namespace App\Constants;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Category;
use App\Models\Area;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Seat;
use App\Models\User;
use Laravel\Jetstream\Rules\Role;

class AuditServices
{
    const Invoices = Invoice::class;
    const Users = User::class;
    const Business = Business::class;
    const Menu = Menu::class;
    const Branches = Branch::class;
    const Roles = Role::class;
    const Holidays = Holiday::class;
    const Categories = Category::class;
    const Items = Item::class;
    const Reservations = Reservation::class;
    const Services = Service::class;
    const Orders = Order::class;
    const Areas = Area::class;
    const Seats = Seat::class;
    const Settings = Setting::class;

}
