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
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->index('class_id');
            $table->index('section_id');
            $table->index('subject_id');
            $table->string('duration', 50)->nullable();
            $table->float('minimum_percentage')->nullable();
            $table->integer('max_attempts')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('is_active');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_exams');
    }
};
