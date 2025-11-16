<?php

return [
    'use_v1' => env('FIREBASE_USE_V1', true),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase/service-account.json')),
    'scopes' => [
        'https://www.googleapis.com/auth/firebase.messaging',
    ],
    // Token cache TTL safety cushion in seconds
    'token_cushion' => 60,
];
