<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id('mission_id');
            $table->string('mission_title')->unique(); // Nama misi harus unik
            $table->text('mission_description')->nullable();
            $table->integer('mission_points')->default(0);

            // Konfigurasi Logic
            // TARGET         : Akumulasi biasa (tidak peduli urutan hari)
            // SEQUENCE       : Progres +1 per hari, TIDAK harus berturut-turut
            // SEQUENCE_STREAK: Progres +1 per hari, HARUS berturut-turut (streak)
            $table->enum('mission_type', ['TARGET', 'SEQUENCE', 'SEQUENCE_STREAK'])->index();
            $table->enum('mission_reset_cycle', ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'])->default('NONE');

            $table->enum('mission_metric_code', [
                'VALIDATION_ACTION', // Mendukung TARGET, SEQUENCE, dan SEQUENCE_STREAK
                'LOGIN_ACTION',      // Hanya SEQUENCE atau SEQUENCE_STREAK
                'PROFILE_UPDATE',    // Hanya TARGET, threshold = 1, cycle = NONE
            ])->index();

            // Untuk TARGET         : Total Amount (jumlah aksi yang harus dikumpulkan)
            // Untuk SEQUENCE       : Days Required (jumlah hari unik yang harus dicapai)
            // Untuk SEQUENCE_STREAK: Days Required (jumlah hari berturut-turut yang harus dicapai)
            $table->integer('mission_threshold');

            $table->boolean('mission_is_active')->default(true);
            $table->timestamps();
        });

        // 2. Tabel Tracker User
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id('user_mission_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('mission_id')->constrained('missions', 'mission_id')->onDelete('cascade');

            $table->integer('user_mission_current_value')->default(0);
            $table->boolean('user_mission_is_completed')->default(false);
            $table->timestamp('user_mission_completed_at')->nullable();

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
