<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ===== Scheduled Tasks =====

// Database backups — daily at midnight Manila time
Schedule::command('backup:database --type=daily')
    ->dailyAt('00:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup-daily.log'));

// Weekly backup — Sundays at 01:00 Manila time
Schedule::command('backup:database --type=weekly')
    ->weeklyOn(0, '01:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup-weekly.log'));

// Monthly backup — 1st of every month at 02:00 Manila time
Schedule::command('backup:database --type=monthly')
    ->monthlyOn(1, '02:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/backup-monthly.log'));

// Queue health check — every hour
Schedule::command('app:check-queue-health')
    ->hourly()
    ->appendOutputTo(storage_path('logs/queue-health.log'));

// Failed jobs monitoring — hourly
Schedule::command('queue:monitor')
    ->hourly()
    ->appendOutputTo(storage_path('logs/queue-monitor.log'));
