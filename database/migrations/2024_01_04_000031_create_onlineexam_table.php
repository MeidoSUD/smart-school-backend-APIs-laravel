<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('exam_name', 255);
            $table->integer('session_id')->unsigned();
            $table->date('exam_date');
            $table->time('exam_start_time');
            $table->time('exam_end_time');
            $table->string('exam_duration', 20);
            $table->integer('full_marks');
            $table->integer('passing_marks');
            $table->decimal('negative_marks', 5, 2);
            $table->text('instructions');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam');
    }
};
