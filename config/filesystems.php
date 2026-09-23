<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),
    'disks' => [
        // Metapodaci u bazi, fajlovi van baze (PRD 12.1). Prelazak na S3 = samo promena FILESYSTEM_DISK=s3 (Laravel Storage facade apstrakcija).
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],
        'documents' => [
            'driver' => 'local',
            'root' => storage_path('app/documents'),
            'throw' => false,
            'report' => false,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],
    ],
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
