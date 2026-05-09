<?php

return [
    'api_key' => env('TRIPAY_API_KEY'),
    'private_key' => env('TRIPAY_PRIVATE_KEY'),
    'merchant_code' => env('TRIPAY_MERCHANT_CODE'),
    'is_production' => filter_var(env('TRIPAY_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => env('TRIPAY_BASE_URL') ?: (
        filter_var(env('TRIPAY_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN)
            ? 'https://tripay.co.id/api'
            : 'https://tripay.co.id/api-sandbox'
    ),
    'default_method' => env('TRIPAY_DEFAULT_METHOD', 'QRIS'),
    'expired_minutes' => (int) env('TRIPAY_EXPIRED_MINUTES', 60),
];
