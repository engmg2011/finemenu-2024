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
        if($request->segment(4) === 'branches' && $request->segment(6) === 'settings' ){
            \request()->merge(['model' => Branch::class]);
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
