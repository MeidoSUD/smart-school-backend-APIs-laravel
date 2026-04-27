<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->string('slug', 150)->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->tinyInteger('is_system')->default(0);
            $table->tinyInteger('is_superadmin')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};