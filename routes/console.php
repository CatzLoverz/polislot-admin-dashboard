<?php

use Illuminate\Support\Facades\Schedule;

// Test scheduler
Schedule::call(function () {
    file_put_contents(
        storage_path('logs/scheduler-test.log'),
        'Scheduler berjalan pada: ' . now() . PHP_EOL,
        FILE_APPEND
    );
})->everyMinute();

// Backup database setiap jam
Schedule::command('db:backup')
    ->hourly()
    ->appendOutputTo(storage_path('logs/backup.log'));

// Cleanup backup lama setiap hari jam 3 pagi
Schedule::command('backup:clean --days=7')
    ->dailyAt('03:00')
    ->appendOutputTo(storage_path('logs/backup-clean.log'));