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
        Schema::table('users', function (Blueprint $table) {
            // Current points - untuk tukar reward (bisa berkurang)
            $table->integer('current_points')->default(0)->after('email');
            
            // Lifetime points - total poin sepanjang masa (tidak pernah berkurang, untuk hitung tier)
            $table->integer('lifetime_points')->default(0)->after('current_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['current_points', 'lifetime_points']);
        });
    }
};