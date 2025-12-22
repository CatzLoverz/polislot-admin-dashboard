<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupDatabaseUser extends Command
{
    /**
     * Nama command artisan.
     */
    protected $signature = 'db:setup-user 
                            {username : Nama user database}
                            {password : Password user database}
                            {host? : Host (default: %)}';

    /**
     * Deskripsi command.
     */
    protected $description = 'Membuat user database user dan menyetel privilege.';

    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $host = $this->argument('host') ?? '%';

        $dbName = config('database.connections.mariadb.database');

        $this->info("Membuat user '$username'@'$host' dan memberikan privilege pada database $dbName...");

        try {
            DB::statement("CREATE USER IF NOT EXISTS '$username'@'$host' IDENTIFIED BY '$password';");

            // === CORE LARAVEL (WRITE) ===
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.cache TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.sessions TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.password_reset_tokens TO '$username'@'$host'");

            // === LARAVEL INTERNAL (READ ONLY) ===
            DB::statement("GRANT SELECT ON $dbName.cache_locks TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.failed_jobs TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.job_batches TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.jobs TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.migrations TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.personal_access_tokens TO '$username'@'$host'");

            // === APPLICATION TABLES ===
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.users TO '$username'@'$host'");

            DB::statement("GRANT SELECT ON $dbName.info_boards TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.park_areas TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.park_subareas TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.park_amenities TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.missions TO '$username'@'$host'");
            DB::statement("GRANT SELECT ON $dbName.rewards TO '$username'@'$host'");

            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.user_missions TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.user_rewards TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.user_histories TO '$username'@'$host'");

            DB::statement("GRANT SELECT ON $dbName.feedback_categories TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.feedbacks TO '$username'@'$host'");

            DB::statement("GRANT SELECT ON $dbName.validations TO '$username'@'$host'");
            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.user_validations TO '$username'@'$host'");

            DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON $dbName.subarea_comments TO '$username'@'$host'");

            DB::statement("FLUSH PRIVILEGES;");

            $this->info("User database berhasil dibuat dan privilege disetel.");
            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Terjadi kesalahan: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
