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
        Schema::create('online_exams', function (Blueprint $table) {
            $table->id();
            $table->string('exam_title');
            $table->string('exam_type')->nullable();
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('subject_id')->nullable();
            $table->string('duration')->nullable();
            $table->float('minimum_percentage')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exams');
    }
};
