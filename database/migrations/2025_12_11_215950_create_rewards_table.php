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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id('reward_id');
            $table->enum('reward_type', ['Voucher', 'Barang']);
            $table->string('reward_name');
            $table->integer('reward_point_required');
            $table->string('reward_image')->nullable();
            $table->timestamps();
        });

        // 2. Tabel Transaksi Klaim User
        Schema::create('user_rewards', function (Blueprint $table) {
            $table->id('user_reward_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('reward_id')->constrained('rewards', 'reward_id')->onDelete('cascade');
            $table->string('user_reward_code')->unique(); // Code unik tiket/voucher
            $table->enum('user_reward_status', ['pending', 'accepted', 'rejected'])->default('pending')->index();
            $table->timestamps();
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
