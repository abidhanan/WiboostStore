<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('wiboost:auto-refund')->hourly()->withoutOverlapping();
Schedule::command('wiboost:check-provider-orders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('wiboost:maintenance-report')->everyTwoHours()->withoutOverlapping();
