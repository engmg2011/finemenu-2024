<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Socialite;

class SocialController extends Controller
{
    /**
     * Redirect to the social provider.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the provider callback.
     */
    public function handleProviderCallback($provider)
    {
        // Get user information from the provider
        $socialUser = Socialite::driver($provider)->user();

        // check if user with the same email exists
        $user = User::where('email', $socialUser->email)->first();
        if ($user) {
            $user->update([
                'provider_id' => $socialUser->id,
                'provider' => $provider,
                'name' => $socialUser->name,
                'email' => $socialUser->email,
                'password' => encrypt($socialUser->id),
                'email_verified_at' => now(),
            ]);
        } else {
            // Find or create a user
            $user = User::updateOrCreate(
                [
                    'provider_id' => $socialUser->id,
                    'provider' => $provider
                ],
                [
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                    'provider' => $provider,
                    'provider_id' => $socialUser->id,
                    'password' => encrypt($socialUser->id),
                    'email_verified_at' => now(),
                ]
            );

        }

        // Generate a token for API authentication
        $token = $user->createToken('Register API Token')->plainTextToken;
        return redirect('/auth/app-token?token=' . $token);
    }

    public function appToken(){
        return "";
    }
}

