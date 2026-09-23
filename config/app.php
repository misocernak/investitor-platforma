<?php

return [
    'name' => env('APP_NAME', 'StructureOps'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'Europe/Belgrade',
    'locale' => 'sr',
    'fallback_locale' => 'en',
    'faker_locale' => 'sr_RS',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [
        ...env('APP_PREVIOUS_KEYS', []),
    ],
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'providers' => Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
    ])->toArray(),
    'aliases' => Illuminate\Support\Facades\Facade::defaultAliases()->merge([])->toArray(),
];
