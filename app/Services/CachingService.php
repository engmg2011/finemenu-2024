<?php

namespace App\Services;

use App;
use App\Models\Business;
use App\Models\Category;

class CachingService
{
    public static function clearMenuCache($category_id = null , $business_id = null)
    {
        $businessBranches = [];

        if($business_id){
            $businessBranches = Business::find($business_id)->branches;
        }
        if(!count($businessBranches)){
            if(isset($category_id)){
                $business_id = Category::find($category_id)->business_id;
                $businessBranches = Business::find($business_id)->branches;
            }
        }
        \Log::debug($businessBranches);
        foreach ($businessBranches as $branch){
            app('Spatie\ResponseCache\ResponseCache')->forget('/api/webapp/branches/'.$branch->slug);
            \Log::debug('Cache cleared for url: '.url('/').'/api/webapp/branches/'.$branch->slug);
        }

    }

}
