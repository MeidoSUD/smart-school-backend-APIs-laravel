<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('class', 100);
            $table->string('section', 100);
            $table->integer('section_id')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
