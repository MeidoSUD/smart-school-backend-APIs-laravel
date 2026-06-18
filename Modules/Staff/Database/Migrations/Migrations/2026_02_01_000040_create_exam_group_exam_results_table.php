<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_exam_results', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('exam_group_class_batch_exam_student_id');

$table->unsignedBigInteger('exam_group_class_batch_exam_subject_id')->nullable();

            $table->index('exam_group_class_batch_exam_student_id', 'idx_exam_result_student');
            $table->index('exam_group_class_batch_exam_subject_id', 'idx_exam_result_subject');
$table->unsignedBigInteger('exam_group_student_id')->nullable();

            $table->string('attendence', 10)->nullable();
            $table->float('get_marks', 10, 2)->nullable()->default(0.00);
            $table->text('note')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_results');
    }
};
