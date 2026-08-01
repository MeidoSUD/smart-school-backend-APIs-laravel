<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->id();
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('staff_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('class_id', 'class_teacher_class_id_index');
            $table->index('section_id', 'class_teacher_section_id_index');
            $table->index('staff_id', 'class_teacher_staff_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};
