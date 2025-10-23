<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_tiers', function (Blueprint $table) {
            // Setiap user hanya punya satu baris (1 tier aktif)
            $table->unsignedBigInteger('user_id')->primary();

            // Tier aktif saat ini (boleh null jika belum punya tier)
            $table->unsignedBigInteger('tier_id')->nullable();

            // Poin total (tidak pernah berkurang)
            $table->unsignedInteger('lifetime_points')->default(0)
                  ->comment('Total poin akumulatif, digunakan untuk menentukan tier');

            // Poin yang bisa ditukar (bisa berkurang)
            $table->unsignedInteger('current_points')->default(0)
                  ->comment('Poin aktif yang bisa ditukar dengan hadiah');

            // Timestamp otomatis
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Relasi ke tabel users
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade');

            // Relasi ke tabel tiers
            $table->foreign('tier_id')
                  ->references('tier_id')
                  ->on('tiers')
                  ->onDelete('set null');

            // Index tambahan untuk tier_id
            $table->index('tier_id', 'idx_user_tiers_tier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_tiers');
    }
};
