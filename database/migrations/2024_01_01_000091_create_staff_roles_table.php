<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->integer('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_roles');
    }
};