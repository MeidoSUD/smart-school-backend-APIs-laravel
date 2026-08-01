<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exams', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_id');
            $table->string('exam_name', 255);
            $table->date('exam_date');
            $table->time('exam_time');
            $table->integer('exam_hour');
            $table->integer('exam_minute');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_group_id', 'exam_group_class_batch_exams_exam_group_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exams');
    }
};
