<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plan_forum', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('subject_syllabus_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('staff_id')->unsigned();
            $table->string('topic', 255);
            $table->text('description');
            $table->date('date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('subject_syllabus_id');
            $table->index('student_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plan_forum');
    }
};
