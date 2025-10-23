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
        Schema::table('tiers', function (Blueprint $table) {
            $table->string('color_theme', 20)->default('blue')->after('min_points');
            $table->string('icon', 50)->default('fa-star')->after('color_theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiers', function (Blueprint $table) {
            $table->dropColumn(['color_theme', 'icon']);
        });
    }
};