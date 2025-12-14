<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('park_areas', function (Blueprint $table) {
            $table->id('park_area_id');
            $table->string('park_area_name');
            $table->string('park_area_code'); // Foto denah/lokasi (opsional)
            $table->json('park_area_data'); 
            
            $table->timestamps();
        });

        Schema::create('park_subareas', function (Blueprint $table) {
            $table->id('park_subarea_id');
            $table->foreignId('park_area_id')->constrained('park_areas', 'park_area_id')->onDelete('cascade');
            $table->string('park_subarea_name');
            $table->json('park_subarea_polygon'); 
            $table->timestamps();
        });

        Schema::create('park_amenities', function (Blueprint $table) {
            $table->id('park_amenity_id');
            $table->foreignId('park_subarea_id')->constrained('park_subareas', 'park_subarea_id')->onDelete('cascade');
            $table->string('park_amenity_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('park_amenities');
        Schema::dropIfExists('park_subareas');
        Schema::dropIfExists('park_areas');
    }
};
