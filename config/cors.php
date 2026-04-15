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

    'paths' => ['api/*'], // Applique CORS à toutes vos routes API
    'allowed_methods' => ['*'], // Autorise toutes les méthodes (GET, POST, etc.)
    'allowed_origins' => [
        'https://gradinchaki08.menjidrc.com', // Votre domaine frontend
        'http://localhost:5173'               // Pour le développement local
    ],
'allowed_headers' => ['*'], // Autorise tous les en-têtes
'supports_credentials' => false, // Passez à true si vous utilisez cookies/sessions
];
