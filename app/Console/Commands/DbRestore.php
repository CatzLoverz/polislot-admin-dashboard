<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DbRestore extends Command
{
    protected $signature = 'db:restore {filename}';
    protected $description = 'Restore database dari file SQL di storage/backups';

    public function handle()
    {
        $filename = $this->argument('filename');
        $filePath = storage_path('app/backups/' . $filename);

        if (!file_exists($filePath)) {
            $this->error("❌ File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $db = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');

        $this->info("⏳ Melakukan restore database '{$db}' dari file '{$filename}'...");

        $passwordPart = $pass ? '-p' . escapeshellarg($pass) : '';

        // Command mysql untuk restore
        $command = sprintf(
            'mysql -h%s -P%s -u%s %s %s < %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $passwordPart,
            escapeshellarg($db),
            escapeshellarg($filePath)
        );

        exec($command, $output, $result);

        if ($result === 0) {
            $this->info("✅ Restore database '{$db}' berhasil!");
        } else {
            $this->error("❌ Restore gagal!");
            $this->line("Detail error:\n" . implode("\n", $output));
        }

        return $result;
    }
}
