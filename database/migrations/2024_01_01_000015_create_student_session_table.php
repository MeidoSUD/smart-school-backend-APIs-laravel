<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_session', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('hostel_room_id')->nullable();
            $table->integer('vehroute_id')->nullable();
            $table->unsignedBigInteger('route_pickup_point_id')->nullable();
            $table->float('transport_fees')->default(0.00);
            $table->float('fees_discount')->default(0.00);
            $table->tinyInteger('is_leave')->default(0);
            $table->string('is_active', 255)->default('no');
            $table->integer('is_alumni')->default(0);
            $table->tinyInteger('default_login')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_session');
    }
};