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

/*
|--------------------------------------------------------------------------
| Jadwal Backup Otomatis (Mode Override)
|--------------------------------------------------------------------------
*/

// 1. BACKUP PER JAM (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'hourly'
Schedule::command('db:backup-auto hourly/hourly-backup.sql') // <-- UBAH DI SINI
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/backup.log'));

// 2. BACKUP HARIAN (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'daily'
Schedule::command('db:backup-auto daily/daily-backup.sql') // <-- UBAH DI SINI
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/backup.log'));

// 3. BACKUP PER 3 HARI (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'every_3days'
Schedule::command('db:backup-auto every_3days/every_3days-backup.sql') // <-- UBAH DI SINI
    ->everyMinute()
    ->appendOutputTo(storage_path('logs/backup.log'));

// -------------------------------------------------------------------------

// CLEANUP (UNTUK BACKUP MANUAL ANDA)
// Perintah ini akan membersihkan backup MANUAL (yang bertanggal)
// dari folder utama 'storage/app/backups'.
// Folder 'hourly', 'daily', dll tidak akan tersentuh.
Schedule::command('backup:clean --days=7')
    ->dailyAt('03:00')
    ->appendOutputTo(storage_path('logs/backup-clean.log'));