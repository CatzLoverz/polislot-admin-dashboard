<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('user_id');
            $table->string('activity_type', 100);
            $table->integer('points_awarded')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Data tambahan seperti tier lama/baru, dll
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index('user_id', 'idx_activity_logs_user_id');
            $table->index('activity_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};