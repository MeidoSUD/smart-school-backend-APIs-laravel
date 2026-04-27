<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_questions', function (Blueprint $table) {
            $table->id();
            $table->integer('question_id')->nullable();
            $table->integer('onlineexam_id')->nullable();
            $table->integer('session_id')->nullable();
            $table->float('marks')->default(0.00);
            $table->float('neg_marks')->default(0.00);
            $table->string('is_active', 1)->default('0');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_questions');
    }
};