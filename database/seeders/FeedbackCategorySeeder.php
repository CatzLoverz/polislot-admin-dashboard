<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeedbackCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('feedback_categories')->insert([
            ['fbk_category_name' => 'Kategori A', 'created_at' => now(), 'updated_at' => now()],
            ['fbk_category_name' => 'Kategori B', 'created_at' => now(), 'updated_at' => now()],
            ['fbk_category_name' => 'Kategori C', 'created_at' => now(), 'updated_at' => now()],
            ['fbk_category_name' => 'Lainnya', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
