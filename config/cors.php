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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://wallone.app',
        'https://web.wallone.app',
        'https://ws.wallone.app',
    ],

    'allowed_origins_patterns' => [
        '/^https:\/\/[\w-]+\.wallone\.app$/',  // Все поддомены wallone.app
        '/^http:\/\/localhost(:\d+)?$/',        // localhost с любым портом
        '/^http:\/\/127\.0\.0\.1(:\d+)?$/',     // 127.0.0.1 с любым портом
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
