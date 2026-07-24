<?php

return [
    'base_url' => env('XOFTWARE_BASE_URL', 'https://payment1.xoftware.id'),
    'api_key' => env('XOFTWARE_API_KEY'),
    'signature_secret' => env('XOFTWARE_API_KEY'),
    'webhook_secret' => env('XOFTWARE_WEBHOOK_SECRET') ?: (env('XOFTWARE_SECRET_KEY') ?: env('XOFTWARE_API_KEY')),
    'merchant_id' => env('XOFTWARE_MERCHANT_ID'),
    'default_channel' => env('XOFTWARE_DEFAULT_CHANNEL', 'QRISREALTIME'),
    'fee_direction' => env('XOFTWARE_FEE_DIRECTION', 'merchant'),
    'expired_minutes' => (int) env('XOFTWARE_EXPIRED_MINUTES', 60),
    'buyer_fee_share' => (float) env('XOFTWARE_BUYER_FEE_SHARE', 0.7),
    'qris_fee_percent' => (float) env('XOFTWARE_QRIS_FEE_PERCENT', 0.007),
    'qris_fee_fixed' => (int) env('XOFTWARE_QRIS_FEE_FIXED', 150),
    'qris_realtime_fee_percent' => (float) env('XOFTWARE_QRIS_REALTIME_FEE_PERCENT', 0.007),
    'qris_realtime_fee_fixed' => (int) env('XOFTWARE_QRIS_REALTIME_FEE_FIXED', 250),
];
