<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule auto-archive of completed memos to run daily at 2 AM
Schedule::command('memos:auto-archive')
    ->dailyAt('02:00')
    ->name('auto-archive-completed-memos')
    ->withoutOverlapping()
    ->runInBackground();

// ===== Super Admin System Scheduled Tasks =====

// Check and update expiring subscriptions status daily at 6:00 AM
Schedule::command('subscriptions:check-expiring')
    ->dailyAt('06:00')
    ->name('check-expiring-subscriptions')
    ->withoutOverlapping()
    ->runInBackground();

// Send renewal reminder notifications daily at 8:00 AM
Schedule::command('subscriptions:send-reminders')
    ->dailyAt('08:00')
    ->name('send-renewal-reminders')
    ->withoutOverlapping()
    ->runInBackground();

// Process auto-renewals daily at 10:00 AM
Schedule::command('subscriptions:process-auto-renewals')
    ->dailyAt('10:00')
    ->name('process-auto-renewals')
    ->withoutOverlapping()
    ->runInBackground();

// Suspend expired subscriptions (beyond grace period) daily at midnight
Schedule::command('subscriptions:suspend-expired')
    ->dailyAt('00:00')
    ->name('suspend-expired-subscriptions')
    ->withoutOverlapping()
    ->runInBackground();

// Process scheduled maintenance every minute to auto-start/end maintenance
Schedule::command('maintenance:process-scheduled')
    ->everyMinute()
    ->name('process-scheduled-maintenance')
    ->withoutOverlapping()
    ->runInBackground();
