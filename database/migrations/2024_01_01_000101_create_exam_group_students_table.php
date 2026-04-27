<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_students', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('student_session_id')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_students');
    }
};