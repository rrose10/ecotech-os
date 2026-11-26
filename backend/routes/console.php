<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Daily database backup at 02:00 (Asia/Dhaka)
Schedule::command('db:backup')->dailyAt('02:00');

// Every day at 03:00: accounts PDFs (last month, any missing month, all-time).
// Daily, so the all-time PDF is always current and a late entry for last month is picked up.
Schedule::command('reports:monthly')->dailyAt('03:00');

// Birthday / work-anniversary wishes on the notice board every morning
Schedule::command('notices:celebrations')->dailyAt('08:00');

// Notifications for notices whose publish date arrived (08:05, after the celebrations)
Schedule::command('notices:announce')->dailyAt('08:05');

// Telegram: connect team members who pressed Start (every minute), and remind
// everyone still checked in 5 minutes before the 6:00 AM shift end.
Schedule::command('telegram:poll')->everyMinute()->withoutOverlapping();
Schedule::command('attendance:remind-checkout')->dailyAt('05:55');
Schedule::command('attendance:remind-dinner')->dailyAt('21:55');
