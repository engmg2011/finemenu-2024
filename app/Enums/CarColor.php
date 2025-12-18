<?php
namespace App\Enums;

class CarColor
{
    public const WHITE  = 'White';
    public const BLACK  = 'Black';
    public const SILVER = 'Silver';
    public const GRAY   = 'Gray';
    public const RED    = 'Red';
    public const BLUE   = 'Blue';
    public const GREEN  = 'Green';
    public const BROWN  = 'Brown';
    public const BEIGE  = 'Beige';
    public const YELLOW = 'Yellow';
    public const ORANGE = 'Orange';
    public const PURPLE = 'Purple';
    public const GOLD   = 'Gold';
    public const BRONZE = 'Bronze';
    public const PINK   = 'Pink';

    /**
     * Get all colors as array
     */
    public static function all(): array
    {
        return [
            self::WHITE,
            self::BLACK,
            self::SILVER,
            self::GRAY,
            self::RED,
            self::BLUE,
            self::GREEN,
            self::BROWN,
            self::BEIGE,
            self::YELLOW,
            self::ORANGE,
            self::PURPLE,
            self::GOLD,
            self::BRONZE,
            self::PINK,
        ];
    }

    public static function labels(): array
    {
        return [
            self::WHITE  => 'أبيض',
            self::BLACK  => 'أسود',
            self::SILVER => 'فضي',
            self::GRAY   => 'رمادي',
            self::RED    => 'أحمر',
            self::BLUE   => 'أزرق',
            self::GREEN  => 'أخضر',
            self::BROWN  => 'بني',
            self::BEIGE  => 'بيج',
            self::YELLOW => 'أصفر',
            self::ORANGE => 'برتقالي',
            self::PURPLE => 'بنفسجي',
            self::GOLD   => 'ذهبي',
            self::BRONZE => 'برونزي',
            self::PINK   => 'وردي',
        ];
    }


    public static function getArColor($color)
    {
        return self::labels()[$color];
    }
}
