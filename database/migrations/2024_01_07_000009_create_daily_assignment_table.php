<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_assignment', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('subject_id');
            $table->integer('class_section_id');
            $table->date('daily_assignment_date');
            $table->string('daily_assignment_title', 255);
            $table->text('details');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('staff_id', 'daily_assignment_staff_id_index');
            $table->index('class_id', 'daily_assignment_class_id_index');
            $table->index('section_id', 'daily_assignment_section_id_index');
            $table->index('subject_id', 'daily_assignment_subject_id_index');
            $table->index('class_section_id', 'daily_assignment_class_section_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_assignment');
    }
};
