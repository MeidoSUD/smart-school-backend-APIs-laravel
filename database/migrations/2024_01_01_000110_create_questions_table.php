<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->string('question_type', 100);
            $table->string('level', 10);
            $table->integer('class_id');
            $table->integer('section_id')->nullable();
            $table->integer('class_section_id')->nullable();
            $table->text('question')->nullable();
            $table->text('opt_a')->nullable();
            $table->text('opt_b')->nullable();
            $table->text('opt_c')->nullable();
            $table->text('opt_d')->nullable();
            $table->text('opt_e')->nullable();
            $table->text('correct')->nullable();
            $table->integer('descriptive_word_limit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};