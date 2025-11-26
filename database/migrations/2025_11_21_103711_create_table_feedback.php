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
    Schema::create('feedback', function (Blueprint $table) {
        $table->id('feedback_id');
        $table->unsignedBigInteger('user_id');
        $table->string('category', 255);
        $table->string('feedback_type', 255);
        $table->string('title', 255);
        $table->text('description')->nullable();
        $table->timestamp('created_at')->useCurrent();
        $table->timestamp('updated_at')->nullable();

        $table->foreign('user_id')
              ->references('user_id')
              ->on('users')
              ->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};