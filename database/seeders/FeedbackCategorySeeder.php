<?php

namespace Database\Seeders;

use App\Models\FeedbackCategory;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FeedbackCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $categories = [];

        // Pertama buat "Lainnya" agar id-nya pertama
        $categories[] = [
            'fbk_category_name' => 'Lainnya',
            'created_at' => $now->copy()->subMinutes(5), // Buat lebih awal sedikit
            'updated_at' => $now->copy()->subMinutes(5),
        ];

        // Buat kategori lainnya
        $otherCategories = ['Bug / Error', 'Fitur Baru', 'Peningkatan UI/UX'];
        
        foreach ($otherCategories as $category) {
            $categories[] = [
                'fbk_category_name' => $category,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Insert semua kategori
        foreach ($categories as $category) {
            FeedbackCategory::create($category);
        }

        $this->command->info('Seeder berhasil: ' . count($categories) . ' kategori feedback telah dibuat.');
        $this->command->info('Kategori "Lainnya" dibuat pertama untuk memastikan urutan dropdown.');
    }
}