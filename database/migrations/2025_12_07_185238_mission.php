<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Induk (Parent)
        Schema::create('missions', function (Blueprint $table) {
            $table->id('mission_id');
            $table->string('mission_title');
            $table->text('mission_description')->nullable();
            $table->integer('mission_points')->default(0);
            $table->enum('mission_type', ['TARGET', 'SEQUENCE'])->index(); 
            $table->enum('mission_metric_code', [
                'VALIDATION_STREAK', // Sequence: Validasi berturut-turut
                'VALIDATION_TOTAL',  // Target: Akumulasi jumlah validasi
                'PROFILE_UPDATE',    // Target: Lengkapi Profil
                'LOGIN_APP',         // Sequence: Login Harian
            ])->index();

            $table->boolean('mission_is_active')->default(true);
            $table->dateTime('mission_start_date')->nullable();
            $table->dateTime('mission_end_date')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Anak: Target (Menyimpan Angka Target)
        Schema::create('mission_targets', function (Blueprint $table) {
            $table->id('mission_target_id');
            $table->foreignId('mission_id')->constrained('missions', 'mission_id')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('mission_target_amount');
            $table->timestamps();
        });

        // 3. Tabel Anak: Sequence (Menyimpan Syarat Hari)
        Schema::create('mission_sequences', function (Blueprint $table) {
            $table->id('mission_sequence_id');
            $table->foreignId('mission_id')->constrained('missions', 'mission_id')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('mission_days_required');
            $table->boolean('mission_is_consecutive')->default(true); // Harus berurut?
            $table->time('mission_reset_time')->default('00:00:00'); // Batas ganti hari
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_sequences');
        Schema::dropIfExists('mission_targets');
        Schema::dropIfExists('missions');
    }
};