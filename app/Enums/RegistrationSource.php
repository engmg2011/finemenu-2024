<?php

namespace App\Enums;

enum RegistrationSource: string
{
    case WEB_APP    = 'webapp';
    case MOBILE_APP = 'mobile_app';
    case DASHBOARD  = 'dashboard';
    case ADMIN      = 'admin';

    public function label(): string
    {
        return match($this) {
            self::WEB_APP    => 'Web Application',
            self::MOBILE_APP => 'Mobile App',
            self::DASHBOARD  => 'Dashboard',
            self::ADMIN      => 'Admin Panel',
        };
    }
}
