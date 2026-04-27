<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_exam_connections', function (Blueprint $table) {
            $table->id();
            $table->integer('exam_group_id')->nullable();
            $table->integer('exam_group_class_batch_exams_id')->nullable();
            $table->float('exam_weightage')->default(0.00);
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_connections');
    }
};