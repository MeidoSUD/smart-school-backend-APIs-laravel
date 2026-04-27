<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('type', 200);
            $table->string('is_active', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};