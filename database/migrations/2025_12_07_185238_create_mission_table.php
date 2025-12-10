<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Master Misi (Single Table)
        Schema::create('missions', function (Blueprint $table) {
            $table->id('mission_id');
            $table->string('mission_title');
            $table->text('mission_description')->nullable();
            $table->integer('mission_points')->default(0);
            
            // Konfigurasi Logic
            $table->enum('mission_type', ['TARGET', 'SEQUENCE'])->index()->comment('Target=Akumulasi, Sequence=Harian');
            $table->enum('mission_reset_cycle', ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'])->default('NONE');
            
            // Metric (Pemicu)
            $table->enum('mission_metric_code', [
                'VALIDATION_STREAK', // Pemicu untuk Sequence
                'VALIDATION_TOTAL',  // Pemicu untuk Target
                'PROFILE_UPDATE',
                'LOGIN_APP',
            ])->index();

            // Untuk Target: Ini adalah "Total Amount"
            // Untuk Sequence: Ini adalah "Days Required"
            $table->integer('mission_threshold'); 

            // Flag Khusus Sequence
            // True: Harus berurut (Streak). False: Boleh bolong-bolong.
            // Untuk Tipe Target, ini diabaikan (set default false).
            $table->boolean('mission_is_consecutive')->default(false);

            $table->boolean('mission_is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Tracker User
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id('user_mission_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('mission_id')->constrained('missions', 'mission_id')->onDelete('cascade');
            
            // Progress saat ini (Angka akumulasi atau Hari ke-berapa)
            $table->integer('user_mission_current_value')->default(0);
            $table->boolean('user_mission_is_completed')->default(false);
            $table->timestamp('user_mmission_completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'mission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
        Schema::dropIfExists('missions');
    }
};