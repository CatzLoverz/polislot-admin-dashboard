<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Traits\LoggableOutput;

class DbList extends Command
{
    use LoggableOutput;

    protected $signature = 'db:list';
    protected $description = 'Menampilkan daftar file backup database di folder storage/backups';

    public function handle()
    {
        $rootBackupPath = storage_path('app/backups');

        if (!is_dir($rootBackupPath)) {
            $this->error("❌ Root folder backup tidak ditemukan: {$rootBackupPath}");
            return Command::FAILURE;
        }

        // Ambil semua subfolder
        $directories = glob($rootBackupPath . '/*', GLOB_ONLYDIR);
        
        // Jika ingin mengecek file di root folder 'backups/' juga, bisa tambahkan logic terpisah atau masukkan ke list.
        // Saat ini asumsi semua backup ada di subfolder (manual, daily, hull, dll).

        if (empty($directories)) {
            $this->warn("⚠️ Belum ada folder backup apapun di {$rootBackupPath}");
            return Command::SUCCESS;
        }

        $foundAny = false;

        foreach ($directories as $dir) {
            $folderName = basename($dir);
            $files = glob($dir . '/*.sql');

            if (empty($files)) {
                continue;
            }

            $foundAny = true;
            $this->info("\n📁 Folder: {$folderName}");

            // Opsional: Urutkan berdasarkan waktu (terbaru diatas)
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach ($files as $index => $file) {
                $filename = basename($file);
                $size = round(filesize($file) / 1024, 2); // KB
                $date = date('Y-m-d H:i:s', filemtime($file));
                
                $this->line("   " . ($index + 1) . ". {$filename} ({$size} KB) [{$date}]");
            }
        }

        if (!$foundAny) {
            $this->warn("⚠️ Tidak ditemukan file backup (.sql) di subfolder manapun.");
        }

        return Command::SUCCESS;
    }
}
