<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_point', function (Blueprint $table) {
            $table->id();
            $table->string('pickup_point', 100);
            $table->string('route_type', 100);
            $table->string('point', 100);
            $table->string('destination', 255);
            $table->string('distance', 50);
            $table->integer('rate');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `pickup_point` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_point');
    }
};
