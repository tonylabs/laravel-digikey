<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Digikey API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Digikey Product Information API V4
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Digikey API credentials. You can obtain these from the Digikey
    | Developer Portal at https://developer.digikey.com/
    |
    */
    'client_id' => env('DIGIKEY_CLIENT_ID'),
    'client_secret' => env('DIGIKEY_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | Base URLs for Digikey API endpoints
    |
    */
    'base_url' => env('DIGIKEY_BASE_URL', 'https://api.digikey.com'),
    'sandbox_url' => env('DIGIKEY_SANDBOX_URL', 'https://sandbox-api.digikey.com'),
    'use_sandbox' => env('DIGIKEY_USE_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | OAuth2 Configuration
    |--------------------------------------------------------------------------
    |
    | OAuth2 settings for API authentication
    |
    */
    'oauth' => [
        'authorization_url' => 'https://api.digikey.com/v1/oauth2/authorize',
        'token_url' => 'https://api.digikey.com/v1/oauth2/token',
        'redirect_uri' => env('DIGIKEY_REDIRECT_URI'),
        'scope' => env('DIGIKEY_SCOPE', 'productinformation'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Locale Settings
    |--------------------------------------------------------------------------
    |
    | Default locale settings for API requests
    |
    */
    'locale' => [
        'language' => env('DIGIKEY_LOCALE_LANGUAGE', 'en'),
        'currency' => env('DIGIKEY_LOCALE_CURRENCY', 'USD'),
        'site' => env('DIGIKEY_LOCALE_SITE', 'US'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Configuration
    |--------------------------------------------------------------------------
    |
    | Customer-specific settings
    |
    */
    'customer_id' => env('DIGIKEY_CUSTOMER_ID'),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching API responses and tokens
    |
    */
    'cache' => [
        'token_key' => 'digikey_access_token',
        'token_ttl' => 3600, // 1 hour in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for the HTTP client
    |
    */
    'http' => [
        'timeout' => env('DIGIKEY_HTTP_TIMEOUT', 30),
        'connect_timeout' => env('DIGIKEY_HTTP_CONNECT_TIMEOUT', 10),
        'retry_attempts' => env('DIGIKEY_HTTP_RETRY_ATTEMPTS', 3),
    ],
];
