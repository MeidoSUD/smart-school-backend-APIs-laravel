<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_houses', function (Blueprint $table) {
            $table->id();
            $table->string('house_name', 200);
            $table->string('description', 400);
            $table->string('is_active', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_houses');
    }
};