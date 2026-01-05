<?php

return [
    'cache' => [
        'enabled' => env('KPI_CACHE_ENABLED', true),
        'ttl' => env('KPI_CACHE_TTL', 3600), // 1 soat
    ],

    'excluded_roles' => ['Admin', 'Super Admin', 'Texnik'],

    'bonus' => [
        'high' => 0.10, // 10% - project avg dan yuqori bo'lsa
        'low' => 0.05,  // 5% - project avg dan past bo'lsa
    ],

    'chunk_size' => env('KPI_CHUNK_SIZE', 1000),

    'date' => [
        'period_start_day' => 26,
        'period_end_day' => 25,
    ],

    'performance' => [
        'use_chunk' => env('KPI_USE_CHUNK', true), // Million+ data uchun
        'eager_loading' => true,
    ],
];