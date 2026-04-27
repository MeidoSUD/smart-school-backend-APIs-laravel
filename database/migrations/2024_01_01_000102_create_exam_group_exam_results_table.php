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
            $table->unsignedBigInteger('exam_group_class_batch_exam_student_id');
            $table->integer('exam_group_class_batch_exam_subject_id')->nullable();
            $table->integer('exam_group_student_id')->nullable();
            $table->string('attendence', 10)->nullable();
            $table->float('get_marks')->default(0.00);
            $table->text('note')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_results');
    }
};