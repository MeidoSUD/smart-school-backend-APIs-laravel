<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_subject_attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->integer('subject_timetable_id')->nullable();
            $table->integer('attendence_type_id')->nullable();
            $table->date('date')->nullable();
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_attendances');
    }
};