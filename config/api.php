<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for API functionality
    |
    */

    'version' => '1.0.0',

    'prefix' => 'api',

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    'rate_limit' => [
        'enabled' => true,
        'requests' => 60,
        'window' => 1, // minutes
    ],

    'cache' => [
        'enabled' => false,
        'ttl' => 3600, // seconds
    ],
];
