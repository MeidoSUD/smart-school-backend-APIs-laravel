<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feemasters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('feetype_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->float('amount')->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feemasters');
    }
};