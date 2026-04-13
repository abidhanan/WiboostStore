<?php

return [
    'pending_alert_minutes' => (int) env('WIBOOST_PENDING_ALERT_MINUTES', 60),
    'processing_alert_minutes' => (int) env('WIBOOST_PROCESSING_ALERT_MINUTES', 120),
    'maintenance_max_items' => (int) env('WIBOOST_MAINTENANCE_MAX_ITEMS', 5),
];
