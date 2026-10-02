<?php

return [

    /* =========================================================
     |  NAMA APLIKASI
     ========================================================= */
    'name' => env('APP_NAME', 'Reservasi Kampus'),

    /* =========================================================
     |  ENVIRONMENT
     ========================================================= */
    'env' => env('APP_ENV', 'production'),

    'debug' => (bool) env('APP_DEBUG', false),

    'url' => env('APP_URL', 'http://localhost'),

    /* =========================================================
     |  TIMEZONE
     ========================================================= */
    'timezone' => 'Asia/Jakarta',

    /* =========================================================
     |  LOCALE
     ========================================================= */
    'locale' => env('APP_LOCALE', 'id'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'id_ID'),

    /* =========================================================
     |  ENCRYPTION
     ========================================================= */
    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /* =========================================================
     |  MAINTENANCE MODE
     ========================================================= */
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

];