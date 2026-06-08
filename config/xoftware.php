<?php

return [
    'base_url' => env('XOFTWARE_BASE_URL', 'https://payment1.xoftware.id'),
    'api_key' => env('XOFTWARE_API_KEY'),
    'signature_secret' => env('XOFTWARE_API_KEY'),
    'webhook_secret' => env('XOFTWARE_WEBHOOK_SECRET') ?: (env('XOFTWARE_SECRET_KEY') ?: env('XOFTWARE_API_KEY')),
    'merchant_id' => env('XOFTWARE_MERCHANT_ID'),
    'default_channel' => env('XOFTWARE_DEFAULT_CHANNEL', 'QRIS'),
    'fee_direction' => env('XOFTWARE_FEE_DIRECTION', 'merchant'),
    'expired_minutes' => (int) env('XOFTWARE_EXPIRED_MINUTES', 60),
];
