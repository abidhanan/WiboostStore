<?php

$publicUrl = trim((string) env('WIBOOST_PUBLIC_URL', ''));

if ($publicUrl === '') {
    $publicUrl = (string) env('APP_URL', 'http://localhost');
}

return [
    'public_url' => rtrim($publicUrl, '/'),
    'admin_contact' => [
        'label' => (string) env('WIBOOST_ADMIN_LABEL', 'Admin Wiboost'),
        'whatsapp' => env('WIBOOST_ADMIN_WHATSAPP'),
        'report_intro' => (string) env('WIBOOST_ADMIN_REPORT_INTRO', 'Halo Admin Wiboost, saya ingin melaporkan kendala di website.'),
    ],
    'pending_alert_minutes' => (int) env('WIBOOST_PENDING_ALERT_MINUTES', 60),
    'processing_alert_minutes' => (int) env('WIBOOST_PROCESSING_ALERT_MINUTES', 120),
    'maintenance_max_items' => (int) env('WIBOOST_MAINTENANCE_MAX_ITEMS', 5),
];
