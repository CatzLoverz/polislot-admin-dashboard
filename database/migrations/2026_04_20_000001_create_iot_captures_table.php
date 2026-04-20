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
        Schema::create('iot_captures', function (Blueprint $table) {
            $table->id('capture_id');
            $table->foreignId('device_id')->constrained('iot_devices', 'device_id')->onDelete('cascade');
            $table->string('capture_image_path');
            $table->boolean('capture_is_trained')->default(false);
            $table->string('capture_ai_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('iot_captures');
    }
};
