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
        // Tabel rewards
        Schema::create('rewards', function (Blueprint $table) {
            $table->id('reward_id');
            $table->string('reward_name', 255);
            $table->text('description')->nullable();
            $table->integer('points_required');
            $table->enum('reward_type', ['merchandise', 'voucher']);
            $table->string('reward_image', 255); // untuk menyimpan path gambar PNG/SVG
            $table->timestamps();
        });

        // Tabel user_rewards
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id('user_reward_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reward_id');
            $table->string('voucher_code', 100)->unique();
            $table->enum('redeemed_status', ['belum dipakai', 'terpakai'])->default('belum dipakai');
            $table->dateTime('redeemed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('reward_id')->references('reward_id')->on('rewards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rewards');
        Schema::dropIfExists('rewards');
    }
};