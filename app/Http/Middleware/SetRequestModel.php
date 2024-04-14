<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Hotel;
use App\Models\Item;
use App\Models\Restaurant;
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
        switch ($request->segment(2)){
            case 'items':
                \request()->merge(['model' => Item::class]);
                break;
            case 'categories':
                \request()->merge(['model' => Category::class]);
                break;
            case 'restaurants':
                \request()->merge(['model' => Restaurant::class]);
                break;
            case 'hotels':
                \request()->merge(['model' => Hotel::class]);
                break;
            case 'users':
                \request()->merge(['model' => User::class]);
                break;
        }

        return $next($request);
    }
}
