<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exam_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_group_class_batch_exam_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_session_id');
            $table->integer('roll_no')->nullable();
            $table->text('teacher_remark')->nullable();
            $table->integer('rank')->default(0);
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exam_students');
    }
};