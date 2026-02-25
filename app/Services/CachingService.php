<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Category;
use Spatie\ResponseCache\ResponseCache;

class CachingService
{
    public function __construct(public ResponseCache $responseCache)
    {
    }

    public function clearMenuCache($category_id = null , $business_id = null)
    {

        app(\Spatie\ResponseCache\ResponseCache::class)->clear();

        // todo : make it depends on branch
        // the error because of the middleware
        $businessBranches = [];
        /*
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
            $this->responseCache->forget('/api/webapp/branches/'.$branch->slug);
            \Log::debug('Cache cleared for url:  /api/webapp/branches/'.$branch->slug);
        }*/

    }

}
