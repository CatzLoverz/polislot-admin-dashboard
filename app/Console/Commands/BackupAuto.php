<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\File; 

class BackupAuto extends Command
{
    protected $signature = 'db:backup-auto {filename}';
    protected $description = 'Backup database (Fleksibel Win/Linux) untuk scheduler';

    public function handle()
    {
        $filename = $this->argument('filename');
        if (!str_ends_with($filename, '.sql')) {
            $filename .= '.sql';
        }

        $config = config('database.connections.mariadb');
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];
        $host     = $config['host'];
        $port     = $config['port'];
        $dumpDir  = $config['dump']['dump_binary_path'] ?? null;

        $this->info(" Running automatic backup (override) to '{$filename}'...");

        $backupFile = storage_path('app/backups' . DIRECTORY_SEPARATOR . $filename);
        $backupDir = File::dirname($backupFile);

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $baseDumperName = 'mariadb-dump';
        $dumperName = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? $baseDumperName . '.exe' : $baseDumperName;
        $dumper = $dumperName; 

        if ($dumpDir) {
            $this->info(" Menggunakan 'dump_binary_path' dari config: {$dumpDir}");
            $dumper = rtrim($dumpDir, '\\/') . DIRECTORY_SEPARATOR . $dumperName;
            
            if (!File::exists($dumper)) {
                $this->error("❌ Executable '{$dumperName}' tidak ditemukan di path: {$dumper}");
                $this->error(" Pastikan 'dump_binary_path' di config/database.php sudah benar.");
                return 1;
            }
        } else {
            $this->info(" 'dump_binary_path' tidak diatur. Mengandalkan '{$dumperName}' dari PATH sistem.");
        }

        $process = Process::fromShellCommandline(
            sprintf(
                '"%s" -h %s -P %s -u %s %s > "%s"',
                $dumper, $host, $port, $username, $database, $backupFile
            ),
            null,
            ['MYSQL_PWD' => $password] 
        );
        
        $process->setTimeout(3600);

        try {
            $process->mustRun(); 

            if (file_exists($backupFile) && filesize($backupFile) > 0) {
                $this->info("✅ Automatic override success: {$filename}");
                return 0;
            } else {
                $this->error("❌ File dibuat tapi kosong (0 bytes).");
                @unlink($backupFile);
                return 1;
            }

        } catch (ProcessFailedException $exception) {
            $this->error("❌ Automatic backup GAGAL! (Override)");
            $this->error("Error: " . $exception->getProcess()->getErrorOutput()); 
            if (file_exists($backupFile)) @unlink($backupFile);
            return 1;
        }
    }
}