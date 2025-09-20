<?php

return [
    'paths' => [
        'api/*',
        'login',
        'logout',
        'register',
        'password/*',
        'email/verify/*',
        'forgot-password',
        'reset-password',
        'sanctum/csrf-cookie',
        'sanctum/*'
    ],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:5174',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:5174'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => [
        'Content-Type',
        'X-Auth-Token',
        'Authorization',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'X-CSRF-TOKEN',
        'Accept',
        'X-Socket-Id'
    ],
    'exposed_headers' => [
        'Authorization',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN'
    ],
    'max_age' => 60 * 60 * 24, // 24 hours
    'supports_credentials' => true,
];
