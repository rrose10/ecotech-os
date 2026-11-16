<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Daily database backup at 02:00 (Asia/Dhaka)
Schedule::command('db:backup')->dailyAt('02:00');

// Birthday / work-anniversary wishes on the notice board every morning
Schedule::command('notices:celebrations')->dailyAt('08:00');

// Notifications for notices whose publish date arrived (08:05, after the celebrations)
Schedule::command('notices:announce')->dailyAt('08:05');
