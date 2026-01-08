<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Traits\LoggableOutput;
use Symfony\Component\Process\Process; 
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File; 

class BackupDatabase extends Command
{
    use LoggableOutput;

    protected $signature = 'db:backup';
    protected $description = 'Backup database MySQL/MariaDB (Fleksibel Win/Linux)';

    public function handle()
    {
        $config = config('database.connections.mariadb');

        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $host     = $config['host'];
        $port     = $config['port'];
        
        // Ambil path dump dari config
        $dumpDir = $config['dump']['dump_binary_path'] ?? null;

        $this->info(" Membuat backup database '{$database}'...");

        $backupFile = storage_path('app/backups/manual' . DIRECTORY_SEPARATOR . 'backup-' . date('Y-m-d-H-i-s') . '.sql');
        $backupDir = File::dirname($backupFile);

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        // 1. Tentukan nama executable berdasarkan OS
        $baseDumperName = 'mariadb-dump';
        $dumperName = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $baseDumperName . '.exe' : $baseDumperName;

        // 2. Tentukan path executable
        $dumper = $dumperName; 

        if ($dumpDir) {
            $this->info(" Menggunakan 'dump_binary_path' dari config: {$dumpDir}");
            $dumper = rtrim($dumpDir, '\\/') . DIRECTORY_SEPARATOR . $dumperName;
            
            // 4. Validasi file ada
            if (!File::exists($dumper)) {
                $this->error("❌ Executable '{$dumperName}' tidak ditemukan di path: {$dumper}");
                $this->error(" Pastikan 'dump_binary_path' di config/database.php sudah benar.");
                return 1;
            }
        } else {
            $this->info(" 'dump_binary_path' tidak diatur. Mengandalkan '{$dumperName}' dari PATH sistem.");
        }

        $this->info(" Menjalankan backup dengan: {$dumper}...");

        $process = Process::fromShellCommandline(
            sprintf(
                '"%s" -h %s -P %s -u %s %s > "%s"',
                $dumper, $host, $port, $username, $database, $backupFile
            ),
            null,
            ['MYSQL_PWD' => $password] 
        );
        
        $process->setTimeout(3600); // Set timeout 1 jam

        try {
            $process->mustRun(); 

            if (file_exists($backupFile) && filesize($backupFile) > 0) {
                $this->logInfo("✅ Database backup created: {$backupFile}");
                $this->logInfo(" Size: " . number_format(filesize($backupFile) / 1024, 2) . " KB");
                return 0;
            } else {
                $this->error("❌ File dibuat tapi kosong (0 bytes) atau tidak ada.");
                @unlink($backupFile);
                return 1;
            }

        } catch (ProcessFailedException $exception) {
            $this->error("❌ Backup GAGAL!");
            $this->error("================= PESAN ERROR (stderr) =================");
            $this->error($exception->getProcess()->getErrorOutput()); 
            $this->error("======================================================");
            $this->error("Pastikan '{$dumper}' valid dan kredensial database benar.");

            if (file_exists($backupFile)) {
                @unlink($backupFile);
            }
            return 1;
        }
    }
}