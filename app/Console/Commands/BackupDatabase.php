<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';
    protected $description = 'Backup database MySQL';

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $this->info(" Membuat backup database '{$database}'...");
        
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        $backupDir = storage_path('app/backups');
        
        // Buat folder jika belum ada
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
            $this->info("✅ Folder backups dibuat");
        }

        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $backupFile = $backupDir . DIRECTORY_SEPARATOR . $filename;
        
        // Cari mysqldump di Laragon
        $mysqldumpPaths = [
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.4.0-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.3.0-winx64\\bin\\mysqldump.exe',
        ];
        
        $mysqldump = null;
        foreach ($mysqldumpPaths as $path) {
            if (file_exists($path)) {
                $mysqldump = $path;
                break;
            }
        }
        
        if (!$mysqldump || !file_exists($mysqldump)) {
            $this->error("❌ mysqldump tidak ditemukan!");
            $this->error(" Cek lokasi MySQL di: C:\\laragon\\bin\\mysql\\");
            return 1;
        }

        // Command mysqldump (tanpa password di command line untuk keamanan)
        $command = sprintf(
            '"%s" -h %s -P %s -u %s -p%s %s > "%s" 2>&1',
            $mysqldump,
            $host,
            $port,
            $username,
            $password,
            $database,
            $backupFile
        );

        $this->info(" Menjalankan backup...");
        exec($command, $output, $returnVar);

        // Cek apakah file benar-benar dibuat
        if (file_exists($backupFile)) {
            $size = filesize($backupFile);
            if ($size > 0) {
                $this->info("✅ Database backup created: {$filename}");
                $this->info(" Size: " . number_format($size / 1024, 2) . " KB");
                $this->info(" Location: {$backupFile}");
                return 0;
            } else {
                $this->error("❌ File dibuat tapi kosong (0 bytes)");
                if (!empty($output)) {
                    $this->error("Output: " . implode("\n", $output));
                }
                @unlink($backupFile);
                return 1;
            }
        } else {
            $this->error("❌ Backup gagal! File tidak dibuat.");
            if (!empty($output)) {
                $this->error("Output: " . implode("\n", $output));
            }
            return 1;
        }
    }
}