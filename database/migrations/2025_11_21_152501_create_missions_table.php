<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TABLE: missions
        Schema::create('missions', function (Blueprint $table) {
            $table->id('mission_id');
            $table->string('mission_name', 100);
            $table->text('description')->nullable();
            $table->string('mission_type', 100); // e.g. 'validation', 'streak', etc.
            $table->integer('target_value')->default(0);
            $table->integer('reward_points')->default(0);

            // Period type wajib
            $table->enum('period_type', ['daily', 'weekly', 'one_time'])->default('one_time');

            // RESET TIME TIDAK NULL
            $table->time('reset_time'); // wajib diisi

            // DATE RANGE TIDAK NULL
            $table->date('start_date');
            $table->date('end_date');

            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        // TABLE: user_missions
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id('user_mission_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('mission_id');

            $table->integer('progress_value')->default(0);
            $table->integer('streak_count')->default(0);

            $table->date('last_completed_date')->nullable();

            $table->boolean('is_completed')->default(false);
            $table->boolean('is_claimed')->default(false);

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('mission_id')->references('mission_id')->on('missions')->onDelete('cascade');

            $table->unique(['user_id', 'mission_id']);

            $table->index('user_id');
            $table->index('mission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
        Schema::dropIfExists('missions');
    }
};
