<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_students', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('event_id');
            $table->string('batch', 100);
            $table->string('current_position', 255);
            $table->string('current_location', 255);
            $table->text('description');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('student_id', 'alumni_students_student_id_index');
            $table->index('event_id', 'alumni_students_event_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_students');
    }
};
