<?php

$allowedOrigins = env('FRONTEND_URLS')
    ? explode(',', env('FRONTEND_URLS'))
    : ['http://localhost:3000'];

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $allowedOrigins,
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
