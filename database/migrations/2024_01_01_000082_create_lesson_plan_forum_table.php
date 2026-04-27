<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plan_forum', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_syllabus_id');
            $table->string('type', 20)->comment('staff,student');
            $table->integer('staff_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->text('message');
            $table->dateTime('created_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plan_forum');
    }
};