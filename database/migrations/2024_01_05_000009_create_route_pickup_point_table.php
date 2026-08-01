<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_pickup_point', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('route_id');
            $table->integer('pickup_point_id');
            $table->string('route_pickup_point', 255);
            $table->time('route_pickup_time');

            $table->timestamps();

            $table->index('route_id');
            $table->index('pickup_point_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_pickup_point');
    }
};
