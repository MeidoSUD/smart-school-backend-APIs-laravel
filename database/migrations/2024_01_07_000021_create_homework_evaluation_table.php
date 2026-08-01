<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_evaluation', function (Blueprint $table) {
            $table->id();
            $table->integer('homework_id');
            $table->integer('student_id');
            $table->integer('student_session_id');
            $table->string('marks', 100);
            $table->text('note');
            $table->date('evaluation_date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('homework_id', 'homework_evaluation_homework_id_index');
            $table->index('student_id', 'homework_evaluation_student_id_index');
            $table->index('student_session_id', 'homework_evaluation_student_session_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_evaluation');
    }
};
