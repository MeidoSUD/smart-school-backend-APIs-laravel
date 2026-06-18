<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id')->nullable();
            $table->unsignedBigInteger('onlineexam_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->float('marks', 10, 2)->default(0.00);
            $table->float('neg_marks', 10, 2)->nullable()->default(0.00);
            $table->string('is_active', 1)->nullable()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_questions');
    }
};
