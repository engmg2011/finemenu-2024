<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to the social provider.
     */
    public function redirectToProvider($provider)
    {
        return response()->json([
            'url' => Socialite::driver($provider)->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    /**
     * Handle the provider callback.
     */
    public function handleProviderCallback($provider)
    {
        try {
            // Get user information from the provider
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Find or create a user
            $user = User::where('provider_id', $socialUser->id)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'provider' => $provider,
                    'provider_id' => $socialUser->id,
                ]);
            }

            // Generate a token for API authentication
            $token = $user->createToken('API Token')->plainTextToken;

            // Return the token and user info
            return response()->json([
                'token' => $token,
                'user' => $user,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed'], 401);
        }
    }
}

