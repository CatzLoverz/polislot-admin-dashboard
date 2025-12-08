<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\FeedbackCategory;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        
        // Pastikan sudah ada kategori
        if (FeedbackCategory::count() == 0) {
            $this->call(FeedbackCategorySeeder::class);
        }

        // Ambil semua kategori
        $categories = FeedbackCategory::all();
        
        $feedbackCount = 0;

        // Buat 2 feedback untuk setiap kategori
        foreach ($categories as $category) {
            for ($i = 1; $i <= 2; $i++) {
                Feedback::create([
                    'fbk_category_id' => $category->fbk_category_id,
                    'feedback_title' => $this->generateTitle($category->fbk_category_name, $i),
                    'feedback_description' => $this->generateDescription($faker, $category->fbk_category_name),
                ]);
                
                $feedbackCount++;
            }
        }

        $this->command->info('Seeder berhasil: ' . $feedbackCount . ' feedback telah dibuat.');
        $this->command->info('Masing-masing kategori memiliki 2 feedback.');
    }

    /**
     * Generate title berdasarkan kategori
     */
    private function generateTitle($categoryName, $index): string
    {
        $titles = [
            'Lainnya' => ['Saran Umum', 'Pertanyaan Lain'],
            'Bug / Error' => ['Laporan Bug ' . $index, 'Masalah Teknis ' . $index],
            'Fitur Baru' => ['Permintaan Fitur ' . $index, 'Ide Pengembangan ' . $index],
            'Peningkatan UI/UX' => ['Masalah UI ' . $index, 'Saran UX ' . $index],
        ];

        return $titles[$categoryName][$index - 1] ?? $categoryName . ' Feedback ' . $index;
    }

    /**
     * Generate description dengan Faker berdasarkan kategori
     */
    private function generateDescription($faker, $categoryName): string
    {
        $descriptions = [
            'Lainnya' => $faker->paragraph(3) . "\n\n" . $faker->sentence(),
            'Bug / Error' => "Saya mengalami masalah berikut:\n" . $faker->paragraph(2) . "\n\nLangkah reproduksi:\n1. " . $faker->sentence() . "\n2. " . $faker->sentence() . "\n3. " . $faker->sentence(),
            'Fitur Baru' => "Saya mengusulkan fitur baru karena:\n" . $faker->paragraph(3) . "\n\nManfaat yang diharapkan:\n" . $faker->sentence(),
            'Peningkatan UI/UX' => "Saran perbaikan UI/UX:\n" . $faker->paragraph(2) . "\n\nAlasan:\n" . $faker->sentence(),
        ];

        return $descriptions[$categoryName] ?? $faker->paragraph(4);
    }
}