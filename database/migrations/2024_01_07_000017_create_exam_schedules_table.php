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
            $table->integer('exam_id');
            $table->integer('subject_id');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_no', 50);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_id', 'exam_schedules_exam_id_index');
            $table->index('subject_id', 'exam_schedules_subject_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
    }
};
