<?php

/**
 * @param array $array
 * @param array $keys
 * @return array
 */

use App\Constants\ConfigurationConstants;
use App\Models\Business;
use App\Models\Menu;
use Carbon\Carbon;

if (!function_exists('array_only')) {
    function array_only(array $array, array $keys): array
    {
        return array_filter($array, function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }
}

if (!function_exists('slug')) {
    function slug(string $name): string
    {
        $business = Menu::where('slug', $name)->first();
        if ($business) {
            $name = $name . '_' . rand(10, 100);
            $business = Menu::where('slug', $name)->first();
            if ($business)
                $name = $name . '_' . rand(10, 100);
        }
        return $name;
    }
}

if (!function_exists('checkUserPermission')) {
    function checkUserPermission($user, $branchId, $service, $action)
    {
        if (!$user->hasPermissionTo("branch.$branchId.$service.$action")) {
            abort(403, "You are not authorized to perform this action.");
        }
    }
}

if (!function_exists('businessToUtcConverter')) {
    function businessToUtcConverter(DateTime|string $dateTime,
                                    int|Business    $business = null,
                                    string          $format = 'Y-m-d\TH:i')
    {
        if (is_int($business))
            $business = Business::find($business);
        $businessTimezone = $business->getConfig(ConfigurationConstants::TIMEZONE, config('app.timezone'));
        if (is_string($dateTime)) {
            try {
                $dateTime = Carbon::createFromFormat($format, $dateTime, $businessTimezone);
            } catch (\Exception $e) {
                \Log::error("error in parsing ". $dateTime );
                $dateTime = Carbon::parse($dateTime, $businessTimezone);
                \Log::error($e);
            }
        }
        return $dateTime->setTimezone('UTC');
    }
}




