<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbList extends Command
{
    protected $signature = 'db:list';
    protected $description = 'Menampilkan daftar file backup database di folder storage/backups';

    public function handle()
    {
        $backupPath = storage_path('backups');

        if (!is_dir($backupPath)) {
            $this->error("❌ Folder backup belum ada: {$backupPath}");
            return Command::FAILURE;
        }

        $files = glob($backupPath . '/*.sql');

        if (empty($files)) {
            $this->warn("⚠️ Belum ada file backup di {$backupPath}");
            return Command::SUCCESS;
        }

        $this->info("📦 Daftar file backup di storage/backups:\n");

        foreach ($files as $index => $file) {
            $filename = basename($file);
            $size = round(filesize($file) / 1024, 2); // KB
            $this->line(($index + 1) . ". {$filename} ({$size} KB)");
        }

        return Command::SUCCESS;
    }
}
