<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exam_subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_class_batch_exam_id');
            $table->integer('subject_id');
            $table->integer('full_marks');
            $table->integer('passing_marks');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_group_class_batch_exam_id', 'exam_group_class_batch_exam_subjects_exam_group_class_b_50ef3293');
            $table->index('subject_id', 'exam_group_class_batch_exam_subjects_subject_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exam_subjects');
    }
};
