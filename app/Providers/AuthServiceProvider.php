<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;                  
use Illuminate\Support\Facades\Log;   

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Daftarkan policy Anda di sini jika ada
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Log untuk memastikan metode boot() di AuthServiceProvider dieksekusi
        // Log::info('[AuthServiceProvider] Metode boot() sedang dieksekusi.');


        /**
         * Gate: access-admin-features
         * Menentukan apakah pengguna boleh mengakses fitur-fitur khusus Admin.
         * Ini bisa digunakan untuk melindungi rute admin atau menampilkan elemen UI khusus Admin.
         */
        Gate::define('access-admin-features', function (User $user) {
            // LOGGING DI DALAM GATE 'access-admin-features'
            // Log::info('[GATE EVALUATION - Admin Features] Memeriksa akses fitur Admin untuk User ID: ' . $user->user_id . ' dengan Peran Aktual: "' . $user->role . '"');

            $isAllowed = strtolower(trim($user->role)) === 'admin'; // Hanya peran 'admin' yang diizinkan

            // if ($isAllowed) {
            //     Log::info('[GATE EVALUATION - Admin Features] Akses Fitur Admin DIIZINKAN untuk User ID: ' . $user->user_id);
            // } else {
            //     Log::warning('[GATE EVALUATION - Admin Features] Akses Fitur Admin DITOLAK untuk User ID: ' . $user->user_id . ' (Peran: "' . $user->role . '")');
            // }
            return $isAllowed;
        });
    }
}