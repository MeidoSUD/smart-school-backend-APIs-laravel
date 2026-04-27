<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hostel_id')->nullable();
            $table->unsignedBigInteger('room_type_id')->nullable();
            $table->string('room_no', 200)->nullable();
            $table->integer('no_of_bed')->nullable();
            $table->float('cost_per_bed')->default(0.00);
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};