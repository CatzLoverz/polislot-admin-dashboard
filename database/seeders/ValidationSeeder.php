<?php

namespace Database\Seeders;

use App\Models\Validation;
use Illuminate\Database\Seeder;

class ValidationSeeder extends Seeder
{
    public function run(): void 
    {
        Validation::create([
            'validation_points' => 100,
            'validation_is_geofence_active' => false
        ]);

        $this->command->info('Seeder Validation berhasil dijalankan');
    }
}

