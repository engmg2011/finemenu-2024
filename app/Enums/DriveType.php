<?php
namespace App\Enums;

class DriveType
{
    public const FWD = 'FWD'; // Front Wheel Drive
    public const RWD = 'RWD'; // Rear Wheel Drive
    public const AWD = 'AWD'; // All Wheel Drive
    public const FOUR_WD = '4WD'; // Four Wheel Drive

    public static function labels(): array
    {
        return [
            self::FWD     => 'دفع أمامي',
            self::RWD     => 'دفع خلفي',
            self::AWD     => 'دفع رباعي دائم',
            self::FOUR_WD => 'دفع رباعي جزئي',
        ];
    }

    public static function label(string $value): string
    {
        return self::labels()[$value] ?? $value;
    }

    public static function all(): array
    {
        return array_keys(self::labels());
    }

}
