<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wiboost:auto-refund')->hourly()->withoutOverlapping();
Schedule::command('wiboost:check-provider-orders')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('wiboost:maintenance-report')->everyTwoHours()->withoutOverlapping();
