<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Restrict which origins may call the API / CSRF-cookie endpoints. By
    | default Laravel ships `allowed_origins => ['*']`; here we lock it down to
    | an explicit, env-driven allow-list (falls back to APP_URL).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(explode(
        ',',
        (string) env('CORS_ALLOWED_ORIGINS', env('APP_URL', ''))
    )),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
