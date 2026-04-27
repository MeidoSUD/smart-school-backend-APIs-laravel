<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->unsignedBigInteger('teacher_subject_id')->nullable();
            $table->date('date_of_exam')->nullable();
            $table->string('start_to', 50)->nullable();
            $table->string('end_from', 50)->nullable();
            $table->string('room_no', 50)->nullable();
            $table->integer('full_marks')->nullable();
            $table->integer('passing_marks')->nullable();
            $table->text('note')->nullable();
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};