<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now = Carbon::now();

        // Buat 1 admin
        User::create([
            'email' => '...', // isi email sebenarnya
            'password' => Hash::make('Password_12'),
            'role' => 'admin',
            'name' => 'Admin PoliSlot',
            'avatar' => null,
            'email_verified_at' => $now,
            'otp_code' => null,
            'otp_expires_at' => null,
            'failed_attempts' => 0,
            'locked_until' => null,
            'current_points' => 0,
            'lifetime_points' => 0,
        ]);

        // Buat 10 user biasa dengan Faker
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('Password_12'),
                'role' => 'user',
                'name' => $faker->name(),
                'avatar' => null,
                'email_verified_at' => $now, 
                'otp_code' => null,
                'otp_expires_at' => null,
                'failed_attempts' => 0,
                'locked_until' => null,
                'current_points' => 0, 
                'lifetime_points' => 0, 
            ]);
        }

        $this->command->info('Seeder berhasil: 1 admin dan 10 user telah dibuat dengan Faker.');
        $this->command->info('User login: gunakan email yang terdaftar dengan password: Password_12');
    }
}