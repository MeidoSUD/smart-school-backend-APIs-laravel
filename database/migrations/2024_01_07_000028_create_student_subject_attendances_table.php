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
            $table->integer('student_id');
            $table->integer('subject_id');
            $table->date('attendance_date');
            $table->integer('attendence_type_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('student_id', 'student_subject_attendances_student_id_index');
            $table->index('subject_id', 'student_subject_attendances_subject_id_index');
            $table->index('attendence_type_id', 'student_subject_attendances_attendence_type_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_subject_attendances');
    }
};
