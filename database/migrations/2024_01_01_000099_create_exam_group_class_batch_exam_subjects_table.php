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
            $table->integer('exam_group_class_batch_exams_id')->nullable();
            $table->integer('subject_id');
            $table->date('date_from');
            $table->time('time_from');
            $table->string('duration', 50);
            $table->string('room_no', 100)->nullable();
            $table->float('max_marks')->nullable();
            $table->float('min_marks')->nullable();
            $table->float('credit_hours')->default(0.00);
            $table->dateTime('date_to')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exam_subjects');
    }
};