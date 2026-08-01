<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_exam_results', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_class_batch_exam_student_id');
            $table->integer('exam_group_class_batch_exam_subject_id');
            $table->integer('obtained_marks');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_group_class_batch_exam_student_id', 'exam_group_exam_results_exam_group_class_batch_exam_stu_f3e6dc56');
            $table->index('exam_group_class_batch_exam_subject_id', 'exam_group_exam_results_exam_group_class_batch_exam_sub_d1648b51');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_results');
    }
};
