<?php

return [

    'secret' => env('RECAPTCHA_SECRET_KEY'),
    'site_key' => env('RECAPTCHA_SITE_KEY'),

    'version' => 'v3', // Use reCAPTCHA v3

    'score_threshold' => 0.5, // Adjust this threshold as needed

    'enabled' => explode(',', env('RECAPTCHA_ENABLED', true))

];
