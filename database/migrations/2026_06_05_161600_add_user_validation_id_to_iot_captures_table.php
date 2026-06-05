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
        Schema::table('iot_captures', function (Blueprint $table) {
            $table->foreignId('user_validation_id')
                ->nullable()
                ->after('capture_ai_status')
                ->constrained('user_validations', 'user_validation_id')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('iot_captures', function (Blueprint $table) {
            $table->dropForeign(['user_validation_id']);
            $table->dropColumn('user_validation_id');
        });
    }
};
