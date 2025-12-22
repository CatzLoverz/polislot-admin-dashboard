<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupDatabaseAdmin extends Command
{
    /**
     * Nama command artisan.
     */
    protected $signature = 'db:setup-admin 
                            {username : Nama user database}
                            {password : Password user database}
                            {host? : Host (default: %)}';

    /**
     * Deskripsi command.
     */
    protected $description = 'Membuat user database admin dan menyetel privilege.';

    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $host = $this->argument('host') ?? '%';

        $dbName = config('database.connections.mariadb.database');

        $this->info("Membuat user '$username'@'$host' dan memberikan privilege pada database '$dbName'...");

        try {
            DB::statement("CREATE USER IF NOT EXISTS '$username'@'$host' IDENTIFIED BY '$password';");

            DB::statement("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$username'@'$host';");

            DB::statement("FLUSH PRIVILEGES;");

            $this->info("User database berhasil dibuat dan privilege disetel.");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
