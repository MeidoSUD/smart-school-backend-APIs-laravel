<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exam_students', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('exam_group_class_batch_exam_id');

$table->unsignedBigInteger('student_id');

            $table->index('exam_group_class_batch_exam_id', 'idx_exam_batch_student_exam');
            $table->index('student_id', 'idx_exam_batch_student_student');
$table->unsignedBigInteger('student_session_id');

            $table->integer('roll_no')->nullable();
            $table->text('teacher_remark')->nullable();
            $table->integer('rank')->default(0);
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exam_students');
    }
};
