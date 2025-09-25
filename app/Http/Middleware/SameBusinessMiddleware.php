<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SameBusinessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $businessId = (int) $request->route('businessId');
        if($businessId !== 0){
            if( (int) auth('sanctum')->user()->business_id !== $businessId ){
                abort(403,"you don't have permission to access this resource");
            }
        }
        return $next($request);
    }
}
