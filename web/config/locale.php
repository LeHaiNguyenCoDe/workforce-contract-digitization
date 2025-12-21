<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Locale
    |--------------------------------------------------------------------------
    |
    | This value is the default locale that will be used by the application
    | when no locale is explicitly set.
    |
    */

    'default' => env('APP_LOCALE', 'vi'),

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    |
    | List of supported locales in the application.
    |
    */

    'supported' => [
        'vi' => [
            'name' => 'Tiếng Việt',
            'native' => 'Tiếng Việt',
            'flag' => '🇻🇳',
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'flag' => '🇬🇧',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used when the requested locale is not available.
    |
    */

    'fallback' => env('APP_FALLBACK_LOCALE', 'vi'),

    /*
    |--------------------------------------------------------------------------
    | Locale Detection Methods
    |--------------------------------------------------------------------------
    |
    | The order in which the application will try to detect the locale:
    | 1. query - From query parameter (?lang=vi)
    | 2. session - From session
    | 3. user - From authenticated user preference
    | 4. header - From Accept-Language header
    | 5. default - Use default locale
    |
    */

    'detection' => [
        'query',
        'session',
        'user',
        'header',
        'default',
    ],
];

