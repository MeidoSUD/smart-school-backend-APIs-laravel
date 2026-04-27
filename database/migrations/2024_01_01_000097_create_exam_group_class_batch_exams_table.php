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
            $table->string('exam', 250)->nullable();
            $table->float('passing_percentage')->nullable();
            $table->integer('session_id');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('exam_group_id')->nullable();
            $table->tinyInteger('use_exam_roll_no')->default(1);
            $table->tinyInteger('is_publish')->default(0);
            $table->tinyInteger('is_rank_generated')->default(0);
            $table->text('description')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exams');
    }
};