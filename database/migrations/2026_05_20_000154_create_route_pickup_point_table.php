<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_pickup_point', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('transport_route_id');
            $table->integer('pickup_point_id');
            $table->float('fees', 10, 2)->nullable()->default(0.00);
            $table->float('destination_distance', 10, 1)->nullable()->default(0.0);
            $table->time('pickup_time')->nullable();
            $table->float('order_number');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_pickup_point');
    }
};
