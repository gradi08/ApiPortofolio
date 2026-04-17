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

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'about', 'projects', 'cv/*'],
    
    'allowed_methods' => ['*'], // Ou ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
    
    'allowed_origins' => [
        'https://gradinchaki.menjidrc.com',
        'https://gradinchaki.menjidrc.com', // Si vous avez aussi le www
        'http://localhost:3000', // Pour le développement local
        'http://localhost:5173', // Pour Vite
    ],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true, // Important pour les cookies/sessions

];
