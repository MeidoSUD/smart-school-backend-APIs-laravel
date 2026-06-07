<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exam_title', 255);
            $table->string('exam_type', 255)->nullable();
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('subject_id')->nullable();
            $table->string('duration', 50)->nullable();
            $table->float('minimum_percentage')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exams');
    }
};
