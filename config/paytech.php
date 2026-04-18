<?php

return [
    'api_key'    => env('PAYTECH_API_KEY', ''),
    'api_secret' => env('PAYTECH_API_SECRET', ''),
    'base_url'   => env('PAYTECH_BASE_URL', 'https://paytech.sn/api'),
    'simulation' => env('PAYTECH_SIMULATION', true),
];
