<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_timetable', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('class_section_id');
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('day', 20)->nullable();
            $table->string('time_from', 20)->nullable();
            $table->string('time_to', 20)->nullable();
            $table->string('room_no', 20)->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->index('class_section_id');
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_timetable');
    }
};
