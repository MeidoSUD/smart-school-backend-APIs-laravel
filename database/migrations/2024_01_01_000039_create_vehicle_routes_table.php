<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_routes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id')->nullable();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_routes');
    }
};