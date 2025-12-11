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
        Schema::create('feedback_categories', function (Blueprint $table) {
            $table->id('fbk_category_id');
            $table->string('fbk_category_name', 255);
            $table->timestamps();

            $table->unique('fbk_category_name');
        });

        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id('feedback_id');
            $table->unsignedBigInteger('fbk_category_id');
            $table->string('feedback_title', 255);
            $table->text('feedback_description');
            $table->timestamps();

            $table->foreign('fbk_category_id')->references('fbk_category_id')->on('feedback_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('feedback_categories');
    }
};