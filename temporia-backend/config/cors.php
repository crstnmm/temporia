<?php

$allowedOrigins = array_filter([
    env('FRONTEND_URL',         'http://localhost:5173'),
    env('FRONTEND_PREVIEW_URL', 'http://localhost:4173'),
]);

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

  'allowed_origins' => [
    'http://localhost:5173',
    'http://localhost:4173'
],
    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
