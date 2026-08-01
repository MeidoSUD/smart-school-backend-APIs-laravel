<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('session_id')->unsigned();
            $table->string('exam_name', 255);
            $table->date('exam_date');
            $table->time('exam_time');
            $table->integer('exam_hour');
            $table->integer('exam_minute');
            $table->integer('full_marks');
            $table->integer('passing_marks');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
