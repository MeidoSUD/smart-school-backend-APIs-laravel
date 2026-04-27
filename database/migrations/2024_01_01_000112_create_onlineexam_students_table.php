<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_students', function (Blueprint $table) {
            $table->id();
            $table->integer('onlineexam_id')->nullable();
            $table->integer('student_session_id')->nullable();
            $table->tinyInteger('is_attempted')->default(0);
            $table->integer('rank')->default(0);
            $table->integer('quiz_attempted')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_students');
    }
};