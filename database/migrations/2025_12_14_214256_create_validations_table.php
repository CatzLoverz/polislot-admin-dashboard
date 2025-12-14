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
        Schema::create('validations', function (Blueprint $table) {
            $table->id('validation_id');
            $table->integer('validation_points');
            $table->timestamps();
        });

        Schema::create('user_validations', function (Blueprint $table) {
            $table->id('user_validation_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('validation_id')->constrained('validations', 'validation_id')->onDelete('cascade');
            $table->foreignId('park_subarea_id')->constrained('park_subareas', 'park_subarea_id')->onDelete('cascade');
            $table->enum('user_validation_content', ['banyak', 'terbatas', 'penuh'])->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {   
        Schema::dropIfExists('user_validations');
        Schema::dropIfExists('validations');
    }
};
