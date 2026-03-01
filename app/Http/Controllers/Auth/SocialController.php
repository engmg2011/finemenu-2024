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
        $callBack = request()->get('CallbackURL', false);
        if($callBack)
            session(['callback' => $callBack]);
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the provider callback.
     */
    public function handleProviderCallback($provider)
    {
        // Get user information from the provider
        $socialUser = Socialite::driver($provider)->stateless()->user();

        // check if user with the same email exists
        $user = User::where('email', $socialUser->email)->first();
        if ($user) {
            $user->update([
                'provider_id' => $socialUser->id,
                'provider' => $provider,
                'name' => $socialUser->name ?? explode('@', $socialUser->email)[0] ?? "No name",
                'email' => $socialUser->email,
                'email_verified_at' => now(),
            ]);
        } else {
            // Find or create a user
            $user = User::create(
                [
                    'name' => $socialUser->name ?? explode('@', $socialUser->email)[0] ?? "No name",
                    'email' => $socialUser->email,
                    'provider' => $provider,
                    'provider_id' => $socialUser->id,
                    'password' => bcrypt($socialUser->id),
                    'email_verified_at' => now(),
                ]
            );

        }
        // Generate a token for API authentication
        $token = $user->createToken('Register API Token')->plainTextToken;
        $callback = session('callback');
        if(isset($callback) && $callback !== ''){
            session()->forget('callback');
            return redirect($callback . '?token=' . $token);
        }
        return redirect('/auth/app-token?token=' . $token);
    }

    public function appToken(){
        return "";
    }
}

