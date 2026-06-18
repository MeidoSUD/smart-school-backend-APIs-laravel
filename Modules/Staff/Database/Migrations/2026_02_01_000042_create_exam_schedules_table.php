<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('session_id');
            $table->index('session_id');
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('teacher_subject_id')->nullable();
            $table->index('teacher_subject_id');
            $table->date('date_of_exam')->nullable();
            $table->string('start_to', 50)->nullable();
            $table->string('end_from', 50)->nullable();
            $table->string('room_no', 50)->nullable();
            $table->integer('full_marks')->nullable();
            $table->integer('passing_marks')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
