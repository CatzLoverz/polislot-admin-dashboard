<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            $table->index(['user_id', 'is_completed', 'is_claimed'], 'idx_user_mission_claim');
            $table->index(['user_id', 'mission_id', 'is_completed'], 'idx_user_mission_progress');
            $table->index('last_completed_date');
        });
        
        Schema::table('missions', function (Blueprint $table) {
            $table->index(['mission_type', 'is_active', 'start_date', 'end_date'], 'idx_mission_active');
        });
    }

    public function down(): void
    {
        Schema::table('user_missions', function (Blueprint $table) {
            $table->dropIndex('idx_user_mission_claim');
            $table->dropIndex('idx_user_mission_progress');
            $table->dropIndex(['last_completed_date']);
        });
        
        Schema::table('missions', function (Blueprint $table) {
            $table->dropIndex('idx_mission_active');
        });
    }
};