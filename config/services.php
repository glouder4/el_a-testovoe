<?php

return [
    'external_api' => [
        'base_url' => env('EXTERNAL_API_BASE_URL', ''),
        'key' => env('EXTERNAL_API_KEY', ''),
        'timeout' => (int) env('EXTERNAL_API_TIMEOUT', 15),
    ],
];
