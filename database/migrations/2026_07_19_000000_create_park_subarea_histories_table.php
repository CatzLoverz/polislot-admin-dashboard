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
        Schema::create('park_subarea_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('park_subarea_id')->constrained('park_subareas', 'park_subarea_id')->onDelete('cascade');
            $table->integer('current_count');
            $table->integer('max_slots');
            $table->enum('status', ['banyak', 'terbatas', 'penuh']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('park_subarea_histories');
    }
};
