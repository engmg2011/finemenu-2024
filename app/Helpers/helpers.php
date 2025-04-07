<?php

/**
 * @param array $array
 * @param array $keys
 * @return array
 */

use App\Models\Menu;
use App\Models\User;

if(!function_exists('array_only')){
    function array_only (Array $array , Array $keys): Array {
        return array_filter($array, function($key) use ($keys) {
            return in_array($key, $keys);
        },ARRAY_FILTER_USE_KEY);
    }
}

if(!function_exists('slug')){
    function slug (string $name): string {
        $business = Menu::where('slug',$name)->first();
        if($business){
            $name = $name.'_'. rand(10,100);
            $business = Menu::where('slug',$name)->first();
            if($business)
                $name = $name.'_'. rand(10,100);
        }
        return $name;
    }
}

if(!function_exists('checkUserPermission')){
    function checkUserPermission (User $user,$branchId, $service, $action){
        if(!$user->hasPermissionTo("branch.$branchId.$service.$action")){
            abort(403, "You are not authorized to perform this action.");
        }
    }
}



