<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exam_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('exam_group_class_batch_exams_id')->nullable();
            $table->integer('subject_id');
            $table->date('date_from');
            $table->time('time_from');
            $table->string('duration', 50);
            $table->string('room_no', 100)->nullable();
            $table->float('max_marks', 10, 2)->nullable();
            $table->float('min_marks', 10, 2)->nullable();
            $table->float('credit_hours', 10, 2)->nullable()->default(0.00);
            $table->dateTime('date_to')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exam_subjects');
    }
};
