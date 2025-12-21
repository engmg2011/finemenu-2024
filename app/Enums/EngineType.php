<?php
namespace App\Enums;

class EngineType
{
    public const PETROL = 'Petrol';
    public const DIESEL = 'Diesel';
    public const HYBRID = 'Hybrid';
    public const ELECTRIC = 'Electric';
    public const PLUGIN_HYBRID = 'Plug-in Hybrid';
    public const LPG = 'LPG';
    public const CNG = 'CNG';

    /**
     * English value => Arabic label
     */
    public static function labels(): array
    {
        return [
            self::PETROL => 'بنزين',
            self::DIESEL => 'ديزل',
            self::HYBRID => 'هجين',
            self::ELECTRIC => 'كهربائي',
            self::PLUGIN_HYBRID => 'هجين قابل للشحن',
            self::LPG => 'غاز مسال',
            self::CNG => 'غاز طبيعي',
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
