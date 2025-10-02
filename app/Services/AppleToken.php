<?php

namespace App\Services;

use Firebase\JWT\JWT;

class AppleToken
{
    public static function generate()
    {
        try {
            $keyFile = storage_path('app/Keys/Apple/AuthKey_' . env('APPLE_KEY_ID') . '.p8');
            $privateKey = file_get_contents($keyFile);
            $claims = [
                'iss' => env('APPLE_TEAM_ID'),
                'iat' => time(),
                'exp' => time() + 86400 * 180, // 6 months
                'aud' => 'https://appleid.apple.com',
                'sub' => env('APPLE_CLIENT_ID'),
            ];

            return JWT::encode($claims, $privateKey, 'ES256', env('APPLE_KEY_ID'));

        } catch (\Exception $e) {
            \Log::error('Could not generate apple token');
        }
        return '';
    }
}
