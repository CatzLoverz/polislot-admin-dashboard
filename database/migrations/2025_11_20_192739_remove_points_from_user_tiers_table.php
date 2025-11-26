<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('user_tiers', function (Blueprint $table) {
            // Hapus kolom poin karena sudah pindah ke tabel users
            $table->dropColumn(['lifetime_points', 'current_points']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tiers', function (Blueprint $table) {
            // Kembalikan kolom jika rollback
            $table->integer('lifetime_points')->default(0);
            $table->integer('current_points')->default(0);
        });
    }
};