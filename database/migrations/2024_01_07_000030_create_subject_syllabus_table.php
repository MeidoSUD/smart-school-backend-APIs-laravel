<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_syllabus', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id');
            $table->integer('subject_group_class_section_id');
            $table->integer('subject_group_subject_id');
            $table->string('topic_name', 255);
            $table->text('syllabus');
            $table->text('homework');
            $table->text('assignment');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('session_id', 'subject_syllabus_session_id_index');
            $table->index('subject_group_class_section_id', 'subject_syllabus_subject_group_class_section_id_index');
            $table->index('subject_group_subject_id', 'subject_syllabus_subject_group_subject_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_syllabus');
    }
};
