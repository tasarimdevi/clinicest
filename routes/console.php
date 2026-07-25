<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// See app/Console/Commands/SendNotificationDigest.php's docblock.
Schedule::command('notifications:digest')->dailyAt('08:00');

// Auto-reassign leads whose clinic response SLA has lapsed — see
// app/Console/Commands/EnforceLeadSla.php. Hourly: fine-grained enough for
// a 24h SLA without hammering, and each run only touches overdue rows.
Schedule::command('leads:enforce-sla')->hourly();
