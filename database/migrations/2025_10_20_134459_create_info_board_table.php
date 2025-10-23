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
        Schema::create('info_board', function (Blueprint $table) {
            $table->id('info_id');
            $table->unsignedBigInteger('admin_id');
            $table->string('title', 255);
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('admin_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->index('admin_id', 'idx_info_board_admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('info_board');
    }
};
