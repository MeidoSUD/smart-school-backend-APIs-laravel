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
            $table->integer('exam_group_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('class_section_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('exam_group_id', 'exam_group_exam_connections_exam_group_id_index');
            $table->index('class_id', 'exam_group_exam_connections_class_id_index');
            $table->index('section_id', 'exam_group_exam_connections_section_id_index');
            $table->index('class_section_id', 'exam_group_exam_connections_class_section_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_connections');
    }
};
