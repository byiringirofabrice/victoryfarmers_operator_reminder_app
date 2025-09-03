<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Example artisan command
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Define schedules here
Schedule::command('queue:prune-failed --hours=26')->daily();
Schedule::command('queue:prune-batches --hours=26')->daily();

// FIXED: Use cron expression for 7 minutes
Schedule::command('tasks:generate-priority')->cron('*/7 * * * *')->withoutOverlapping();

Schedule::command('tasks:generate')->everyTenMinutes()->withoutOverlapping();
Schedule::command('tasks:cleanup')->daily()->withoutOverlapping();

Schedule::command('tasks:generate-kenyas')->everyThirtyMinutes()->withoutOverlapping();
Schedule::command('tasks:generate-other-countries')->everyFiveMinutes()->withoutOverlapping();