<?php

/*
 * Configuració CORS per permetre que el panell admin
 * (admin.inclinio.localhost) pugui parlar amb l'API
 * compartint cookies de sessió Sanctum.
 */
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://admin.inclinio.localhost',
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
