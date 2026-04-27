<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_pickup_point', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_route_id');
            $table->unsignedBigInteger('pickup_point_id');
            $table->float('fees')->default(0.00);
            $table->float('destination_distance')->default(0.0);
            $table->time('pickup_time')->nullable();
            $table->float('order_number');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_pickup_point');
    }
};