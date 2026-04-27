<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id')->nullable();
            $table->text('exam')->nullable();
            $table->integer('attempt');
            $table->dateTime('exam_from')->nullable();
            $table->dateTime('exam_to')->nullable();
            $table->integer('is_quiz')->default(0);
            $table->dateTime('auto_publish_date')->nullable();
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->time('duration');
            $table->float('passing_percentage')->default(0);
            $table->text('description')->nullable();
            $table->integer('publish_result')->default(0);
            $table->integer('answer_word_count')->default(-1);
            $table->string('is_active', 1)->default('0');
            $table->integer('is_marks_display')->default(0);
            $table->integer('is_neg_marking')->default(0);
            $table->integer('is_random_question')->default(0);
            $table->tinyInteger('is_rank_generated')->default(0);
            $table->tinyInteger('publish_exam_notification');
            $table->tinyInteger('publish_result_notification');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam');
    }
};