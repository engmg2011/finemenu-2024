<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Item;
use App\Models\Business;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRequestModel
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $businessId = (int) $request->route('businessId');
        if($businessId !== 0){
            if( (int) auth('sanctum')->user()->business_id !== $businessId ){
                abort(403,"you don't have permission to access this resource");
            }
        }

        if($request->segment(4) === 'branches' && $request->segment(6) === 'settings' ){
            \request()->merge(['model' => Branch::class]);
            return $next($request);
        }

        // segments 0  / 1 / 2     /3 / 4       /5 /6
        // https://.. /api/business/1/categories/24/settings/set
        if($request->segment(4) === 'categories' && $request->segment(6) === 'settings' ){
            \request()->merge(['model' => Category::class]);
            return $next($request);
        }

        switch ($request->segment(2)){
            case 'items':
                \request()->merge(['model' => Item::class]);
                break;
            case 'categories':
                \request()->merge(['model' => Category::class]);
                break;
            case 'business':
                \request()->merge(['model' => Business::class]);
                break;
            case 'users':
                \request()->merge(['model' => User::class]);
                break;
        }

        return $next($request);
    }
}
