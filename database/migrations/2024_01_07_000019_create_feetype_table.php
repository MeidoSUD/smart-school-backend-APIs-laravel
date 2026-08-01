<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feetype', function (Blueprint $table) {
            $table->id();
            $table->string('fees_type', 100);
            $table->string('description', 255);
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feetype');
    }
};
