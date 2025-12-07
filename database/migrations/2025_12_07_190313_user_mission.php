<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id('user_mission_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('mission_id')->constrained('missions', 'mission_id')->onUpdate('cascade')->onDelete('cascade');
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
    }
};