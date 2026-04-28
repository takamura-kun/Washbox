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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',           // React/Next.js development
        'http://localhost:8080',           // Vue development
        'http://localhost:19006',          // Expo development
        'http://localhost:8081',           // Expo web development
        'http://localhost:8000',           // Laravel backend
        'http://192.168.1.9:8000',         // Local network testing
        'http://192.168.1.9:8081',         // Expo mobile on local network
        'exp://192.168.1.9:8081',          // Expo mobile app
        'exp://localhost:8081',            // Expo mobile localhost
        // Add your production domains here:
        // 'https://washbox.com',
        // 'https://admin.washbox.com',
        // 'https://api.washbox.com',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,  // ✅ Important for Sanctum

];
