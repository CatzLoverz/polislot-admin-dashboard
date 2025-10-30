<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupClean extends Command
{
    protected $signature = 'backup:clean {--days=7 : Hapus backup yang lebih tua dari jumlah hari ini}';

    protected $description = 'Hapus file backup lama dari folder storage/app/backups';

    public function handle()
    {
        $days = (int) $this->option('days');
        $path = storage_path('app/backups'); // ✅ UBAH DI SINI

        if (!File::exists($path)) {
            $this->error("❌ Folder {$path} tidak ditemukan.");
            return Command::FAILURE;
        }

        $this->info("🧹 Membersihkan file backup lebih dari {$days} hari di {$path} ...");

        $files = File::files($path);
        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Carbon::createFromTimestamp(File::lastModified($file));
            if ($lastModified->lt(Carbon::now()->subDays($days))) {
                File::delete($file);
                $deletedCount++;
                $this->line("🗑️  Dihapus: {$file->getFilename()} (tanggal: {$lastModified->toDateString()})");
            }
        }

        if ($deletedCount === 0) {
            $this->info("✅ Tidak ada file lama yang perlu dihapus.");
        } else {
            $this->info("✅ Total {$deletedCount} file backup lama berhasil dihapus.");
        }

        return Command::SUCCESS;
    }
}