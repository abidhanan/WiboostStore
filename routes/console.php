<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wiboost:auto-refund')->hourly()->withoutOverlapping();
Schedule::command('wiboost:check-provider-orders --limit=50')->everyMinute()->withoutOverlapping();
Schedule::command('wiboost:sync-tripay-deposits --limit=200')->everyMinute()->withoutOverlapping();
Schedule::command('wiboost:maintenance-report --force')->everyTwoHours()->withoutOverlapping();
Schedule::command('queue:work --queue=emails --stop-when-empty --max-jobs=25 --max-time=50 --tries=3 --timeout=45')->everyMinute()->withoutOverlapping();
