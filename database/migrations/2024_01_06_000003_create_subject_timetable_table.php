<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_timetable', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('subject_group_class_section_id');
            $table->integer('subject_id');
            $table->integer('staff_id');
            $table->string('day', 10);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_no', 50);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('subject_group_class_section_id');
            $table->index('subject_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_timetable');
    }
};
