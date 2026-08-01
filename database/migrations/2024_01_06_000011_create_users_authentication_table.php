<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_authentication', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('user_id');
            $table->string('token', 255);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_authentication');
    }
};
