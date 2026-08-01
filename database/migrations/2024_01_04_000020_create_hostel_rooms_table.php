<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('hostel_id')->unsigned();
            $table->integer('room_type_id')->unsigned();
            $table->string('hostel_room_no', 50);
            $table->integer('hostel_room_capacity');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('hostel_id');
            $table->index('room_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
