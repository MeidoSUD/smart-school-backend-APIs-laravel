<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_students', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_id');
            $table->integer('student_id');
            $table->integer('student_session_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_group_id', 'exam_group_students_exam_group_id_index');
            $table->index('student_id', 'exam_group_students_student_id_index');
            $table->index('student_session_id', 'exam_group_students_student_session_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_students');
    }
};
