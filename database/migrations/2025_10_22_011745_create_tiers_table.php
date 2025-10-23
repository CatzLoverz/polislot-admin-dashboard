<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id('tier_id');
            $table->string('tier_name', 50)->unique();
            $table->unsignedInteger('min_points')->default(0)
                  ->comment('Minimal lifetime_points untuk mencapai tier ini');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
