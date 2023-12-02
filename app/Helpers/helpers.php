<?php

/**
 * @param array $array
 * @param array $keys
 * @return array
 */

use App\Models\Restaurant;

if(!function_exists('array_only')){
    function array_only (Array $array , Array $keys): Array {
        return array_filter($array, function($key) use ($keys) {
            return in_array($key, $keys);
        },ARRAY_FILTER_USE_KEY);
    }
}

if(!function_exists('slug')){
    function slug (string $name): string {
        $restaurant = Restaurant::where('slug',$name)->first();
        if($restaurant){
            $name = $name.'_'. rand(10,100);
            $restaurant = Restaurant::where('slug',$name)->first();
            if($restaurant)
                $name = $name.'_'. rand(10,100);
        }
        return $name;
    }
}





