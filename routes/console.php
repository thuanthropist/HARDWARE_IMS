<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('inventory:check-expiries')->dailyAt('07:00');
Schedule::command('inventory:check-low-stock')->dailyAt('07:15');
Schedule::command('quotes:expire')->dailyAt('06:00');
Schedule::command('quotes:notify-expiring')->dailyAt('07:30');
Schedule::command('license:heartbeat')->weekly()->mondays()->at('03:00');

// backup.frequency ('none'|'daily'|'weekly') is read fresh every time this
// file loads (every artisan invocation, including each schedule:run tick),
// so changing it in Admin Settings takes effect without a deploy. Guarded
// by hasTable() since this file also loads before the settings table
// exists on a fresh install (e.g. during the first migrate).
if (Schema::hasTable('settings')) {
    match (setting('backup.frequency', 'none')) {
        'daily' => Schedule::command('backup:run')->dailyAt('03:30'),
        'weekly' => Schedule::command('backup:run')->weekly()->mondays()->at('03:30'),
        default => null,
    };
}
