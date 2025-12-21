<?php
namespace App\Enums;

class CarShapeType
{

    public const SEDAN        = 'Sedan';
    public const SUV          = 'SUV';
    public const HATCHBACK    = 'Hatchback';
    public const COUPE        = 'Coupe';
    public const CONVERTIBLE  = 'Convertible';
    public const WAGON        = 'Wagon';
    public const PICKUP       = 'Pickup';
    public const VAN          = 'Van';
    public const MINIVAN      = 'Minivan';
    public const CROSSOVER    = 'Crossover';

    /**
     * English value => Arabic label
     */
    public static function labels(): array
    {
        return [
            self::SEDAN       => 'سيدان',
            self::SUV         => 'رياضية (SUV)',
            self::HATCHBACK   => 'هاتشباك',
            self::COUPE       => 'كوبيه',
            self::CONVERTIBLE => 'مكشوفة',
            self::WAGON       => 'ستيشن واجن',
            self::PICKUP      => 'بيك أب',
            self::VAN         => 'فان',
            self::MINIVAN     => 'ميني فان',
            self::CROSSOVER   => 'كروس أوفر',
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
