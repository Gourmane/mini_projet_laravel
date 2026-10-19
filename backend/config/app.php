<?php
return [
    'name' => env('APP_NAME', 'IT Stock Manager'), 'env' => env('APP_ENV', 'local'),
    'debug' => (bool) env('APP_DEBUG', false), 'url' => env('APP_URL', 'http://localhost:8000'),
    'timezone' => 'Africa/Casablanca', 'locale' => 'fr', 'fallback_locale' => 'en',
    'faker_locale' => 'fr_FR', 'cipher' => 'AES-256-CBC', 'key' => env('APP_KEY'),
    'previous_keys' => [], 'maintenance' => ['driver' => 'file'],
];
