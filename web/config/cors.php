<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'api/v1/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter([
        'http://localhost:3000',
        'http://localhost:5173',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:5173',
        // Local network IPs only in development
        env('APP_ENV') === 'local' ? 'http://192.168.1.10:3000' : null,
        env('APP_ENV') === 'local' ? 'http://192.168.1.10:5173' : null,
        env('APP_ENV') === 'local' ? 'http://192.168.1.10' : null,
    ]),

    // SECURITY: Only allow local network patterns in development environment
    'allowed_origins_patterns' => env('APP_ENV') === 'local' ? [
        '/^http:\/\/192\.168\.\d+\.\d+(:\d+)?$/',
    ] : [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];

