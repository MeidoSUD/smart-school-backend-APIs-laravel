<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_timetable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('session_id')->nullable();
            $table->integer('class_id')->nullable();
            $table->integer('section_id')->nullable();
            $table->integer('subject_group_id')->nullable();
            $table->integer('subject_group_subject_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->string('day', 20)->nullable();
            $table->string('time_from', 20)->nullable();
            $table->string('time_to', 20)->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room_no', 20)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_timetable');
    }
};
