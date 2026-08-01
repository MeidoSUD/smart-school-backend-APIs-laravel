<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('subject_id');
            $table->integer('staff_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('class_section_id');
            $table->string('question_title', 255);
            $table->string('question_type', 50);
            $table->string('option_a', 255);
            $table->string('option_b', 255);
            $table->string('option_c', 255);
            $table->string('option_d', 255);
            $table->string('correct_answer', 255);
            $table->text('explanation');
            $table->integer('marks');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('subject_id');
            $table->index('staff_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
