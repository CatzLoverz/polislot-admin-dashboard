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
            $table->string('mission_title');
            $table->text('mission_description')->nullable();
            $table->integer('mission_points')->default(0);
            
            // Konfigurasi Logic
            $table->enum('mission_type', ['TARGET', 'SEQUENCE'])->index();
            $table->enum('mission_reset_cycle', ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'])->default('NONE');
            
            $table->enum('mission_metric_code', [
                'VALIDATION_ACTION', // Menangani baik Total maupun Streak Validasi
                'LOGIN_ACTION',      // Menangani Login Harian
                'PROFILE_UPDATE',    // Menangani Update Profil
            ])->index();

            // Untuk Target: Ini adalah "Total Amount"
            // Untuk Sequence: Ini adalah "Days Required"
            $table->integer('mission_threshold'); 

            // Flag Khusus Sequence
            $table->boolean('mission_is_consecutive')->default(false);

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