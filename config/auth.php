<?php

use App\Models\User;

return [

    /* =========================================================
     |  DEFAULTS
     ========================================================= */
    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /* =========================================================
     |  GUARDS
     ========================================================= */
    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'users',
        ],
    ],

    /* =========================================================
     |  USER PROVIDERS
     ========================================================= */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', User::class),
        ],
    ],

    /* =========================================================
     |  PASSWORD RESET
     ========================================================= */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire'   => 60,
            'throttle' => 60,
        ],
    ],

    /* =========================================================
     |  PASSWORD CONFIRMATION TIMEOUT
     ========================================================= */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];