<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_syllabus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('topic_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('created_for');
            $table->date('date');
            $table->string('time_from', 255);
            $table->string('time_to', 255);
            $table->text('presentation');
            $table->text('attachment');
            $table->string('lacture_youtube_url', 255);
            $table->string('lacture_video', 255);
            $table->text('sub_topic');
            $table->text('teaching_method');
            $table->text('general_objectives');
            $table->text('previous_knowledge');
            $table->text('comprehensive_questions');
            $table->integer('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_syllabus');
    }
};