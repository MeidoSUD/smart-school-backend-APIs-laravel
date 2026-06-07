<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exam_results', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('online_exam_id');
            $table->integer('student_id');
            $table->text('answers')->nullable();
            $table->float('obtained_marks')->nullable();
            $table->dateTime('attended_on')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exam_results');
    }
};
