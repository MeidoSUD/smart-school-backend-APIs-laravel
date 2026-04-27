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
            $table->string('name', 255);
            $table->string('latitude', 100)->nullable();
            $table->string('longitude', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_point');
    }
};