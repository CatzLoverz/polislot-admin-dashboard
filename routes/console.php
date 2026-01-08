<?php

use Illuminate\Support\Facades\Schedule;

// 1. BACKUP PER JAM (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'hourly'
Schedule::command('db:backup-auto hourly/hourly-backup.sql')
    ->hourly();

// 2. BACKUP HARIAN (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'daily'
Schedule::command('db:backup-auto daily/daily-backup.sql')
    ->daily();

// 3. BACKUP PER 3 HARI (OVERRIDE)
// Akan menimpa file 'backup.sql' di dalam folder 'every_3days'
Schedule::command('db:backup-auto every_3days/every_3days-backup.sql')
    ->cron('0 0 */3 * *');

// 4. CLEAN UP BACKUP MANUAL LAMA (OLDER THAN 7 DAYS)
Schedule::command('backup:clean --days=7')
    ->dailyAt('03:00');