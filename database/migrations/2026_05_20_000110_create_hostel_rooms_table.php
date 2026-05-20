<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('hostel_id')->nullable();
            $table->integer('room_type_id')->nullable();
            $table->string('room_no', 200)->nullable();
            $table->integer('no_of_bed')->nullable();
            $table->float('cost_per_bed', 10, 2)->nullable()->default(0.00);
            $table->string('title', 200)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel_rooms');
    }
};
