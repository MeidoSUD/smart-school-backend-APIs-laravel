<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_tutorial', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->string('title', 255);
            $table->text('description');
            $table->string('link', 255);
            $table->integer('staff_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('subject_id');
            $table->integer('class_section_id');
            $table->date('video_date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('staff_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('subject_id');
            $table->index('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_tutorial');
    }
};
