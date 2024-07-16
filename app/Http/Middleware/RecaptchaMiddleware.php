<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use ReCaptcha\ReCaptcha;

class RecaptchaMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if reCAPTCHA is enabled for this route
        if (config('recaptcha.enabled', false)) {
            $recaptcha = new ReCaptcha(config('recaptcha.secret'));
            $response = $recaptcha->verify($request->input('g-recaptcha-response'), $_SERVER['REMOTE_ADDR']);
            if (!$response->isSuccess() || $response->getScore() < config('recaptcha.score_threshold')) {
                return response()->json(['error' => 'reCAPTCHA verification failed'], 400);
            }
        }

        return $next($request);
    }
}
