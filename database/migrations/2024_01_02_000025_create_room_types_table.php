<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_types', function (Blueprint $table) {
            $table->id();
            $table->string('room_type', 100);
            $table->string('capacity', 100);
            $table->integer('rent_per_room');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `room_types` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('room_types');
    }
};
