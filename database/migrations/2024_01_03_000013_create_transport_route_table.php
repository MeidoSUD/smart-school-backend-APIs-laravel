<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_route', function (Blueprint $table) {
            $table->id();
            $table->string('route_name', 100);
            $table->string('route_pickup_point', 255);
            $table->time('route_pickup_time');
            $table->string('route_droping_point', 255);
            $table->time('route_droping_time');
            $table->integer('route_other_charge');
            $table->text('route_desc');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `transport_route` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `transport_route` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_route');
    }
};
