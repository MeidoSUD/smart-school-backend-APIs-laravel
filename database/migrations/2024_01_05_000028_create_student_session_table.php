<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_session', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('session_id');
            $table->integer('student_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('section_id_old');
            $table->integer('class_id_old');
            $table->integer('route_id');
            $table->integer('vehicle_id');
            $table->integer('route_pickup_point_id');
            $table->integer('hostel_room_id');
            $table->integer('hostel_id');

            $table->timestamps();

            $table->index('session_id');
            $table->index('student_id');
            $table->index('class_id');
            $table->index('section_id');
            $table->index('route_pickup_point_id');
            $table->index('hostel_room_id');
            $table->index('route_id');
            $table->index('vehicle_id');
            $table->index('hostel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_session');
    }
};
