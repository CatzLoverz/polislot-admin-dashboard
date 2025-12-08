<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\FeedbackCategory;
use App\Models\Feedback;


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

        Schema::enableForeignKeyConstraints();

        $this->call([
            UserSeeder::class,
            FeedbackCategorySeeder::class,
            FeedbackSeeder::class
        ]);
    }
}
