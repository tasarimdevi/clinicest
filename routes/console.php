<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// See app/Console/Commands/SendNotificationDigest.php's docblock.
Schedule::command('notifications:digest')->dailyAt('08:00');
