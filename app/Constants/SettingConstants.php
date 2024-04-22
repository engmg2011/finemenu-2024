<?php

namespace App\Constants;

use Illuminate\Validation\Rules\Enum;

class SettingConstants
{
    const primary = "primary";
    const accent = "accent";
    const textPrimary = "textPrimary";
    const textSecondary = "textSecondary";
    const dark = "dark";
    const light = "light";

    const defaultValues = [
        'primary' => "E29526FF",
        'accent' => "F93937FF",
        'textPrimary' => "1D1D1DFF",
        'textSecondary' => "5E5E5EFF",
        'dark' => "57390EFF",
        'light' => "EFEFEFFF"
    ];

    const Keys = [
        'SHIFTS' => 'SHIFTS',
        'WORK_DAYS' => 'WORK_DAYS'
    ];

    const WORK_DAYS = [
      "Sat", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri"
    ];
}
