<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('teacher_id');
            $table->integer('subject_id');
            $table->integer('class_section_id');
            $table->integer('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};
