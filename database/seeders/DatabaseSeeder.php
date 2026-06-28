<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Feedback;
use App\Models\FeedbackCategory;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        User::truncate();
        FeedbackCategory::truncate();
        Feedback::truncate();
        Validation::truncate();

        Schema::enableForeignKeyConstraints();

        $this->call([
            UserSeeder::class,
            FeedbackCategorySeeder::class,
            FeedbackSeeder::class,
            ValidationSeeder::class,
        ]);
    }
}
