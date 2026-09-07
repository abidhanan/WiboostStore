<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Digiflazz H2H Configuration
    |--------------------------------------------------------------------------
    |
    | Credential untuk integrasi API Digiflazz (PPOB & Top Up Game).
    | Pastikan variabel ini sudah terdaftar di file .env Anda.
    |
    */

    'digiflazz' => [
        'username' => env('DIGIFLAZZ_USERNAME'),
        'key'      => env('DIGIFLAZZ_KEY'),
        'base_url' => env('DIGIFLAZZ_BASE_URL', 'https://api.digiflazz.com/v1'),
    ],

    'ordersosmed' => [
        'api_url' => env('ORDERSOSMED_API_URL'),
        'api_id'  => env('ORDERSOSMED_API_ID'),
        'api_key' => env('ORDERSOSMED_API_KEY'),
        'secret_key' => env('ORDERSOSMED_SECRET_KEY'),
    ],

    'discord' => [
        'webhook_url' => env('DISCORD_WEBHOOK_URL'),
        'webhooks' => [
            'transactions'        => env('DISCORD_WEBHOOK_TRANSACTIONS_URL'),
            'manual_transactions' => env('DISCORD_WEBHOOK_MANUAL_TRANSACTIONS_URL', env('DISCORD_WEBHOOK_MANUAL_TRANSACTION_URL')),
            'deposits'            => env('DISCORD_WEBHOOK_DEPOSITS_URL', env('DISCORD_WEBHOOK_DEPOSIT_URL')),
            'withdrawals'         => env('DISCORD_WEBHOOK_WITHDRAWALS_URL', env('DISCORD_WEBHOOK_WITHDRAWAL_URL', env('DISCORD_WEBHOOK_PENARIKAN_URL'))),
            'refunds'             => env('DISCORD_WEBHOOK_REFUNDS_URL'),
            'reports'             => env('DISCORD_WEBHOOK_REPORTS_URL'),
            'stock'               => env('DISCORD_WEBHOOK_STOCK_URL'),
            'system'              => env('DISCORD_WEBHOOK_SYSTEM_URL', env('DISCORD_WEBHOOK_SYSTEMS_URL', env('DISCORD_WEBHOOK_ERRORS_URL'))),
        ],
        'username'    => env('DISCORD_BOT_NAME', 'Wiboost Bot'),
        'avatar_url'  => env('DISCORD_BOT_AVATAR_URL'),
    ],

];
