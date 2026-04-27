<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_no', 20)->nullable();
            $table->string('vehicle_model', 100)->default('None');
            $table->string('vehicle_photo', 255)->nullable();
            $table->string('manufacture_year', 4)->nullable();
            $table->string('registration_number', 50);
            $table->string('chasis_number', 100);
            $table->string('max_seating_capacity', 255);
            $table->string('driver_name', 50)->nullable();
            $table->string('driver_licence', 50)->default('None');
            $table->string('driver_contact', 20)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};