<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Socialite;

class SocialController extends Controller
{
    /**
     * Redirect the user to the OAuth provider.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function handleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->user();

        // Check if the user already exists in the database
        $user = User::where('provider_id', $socialUser->id)->first();

        if (!$user) {
            // If not, create a new user
            $user = User::create([
                'name' => $socialUser->name,
                'email' => $socialUser->email,
                'provider' => $provider,
                'provider_id' => $socialUser->id,
                // Add additional fields as needed
            ]);
        }

        // Log the user in
        Auth::login($user);

        // Redirect to the desired location
        return redirect('/home');
    }
}

