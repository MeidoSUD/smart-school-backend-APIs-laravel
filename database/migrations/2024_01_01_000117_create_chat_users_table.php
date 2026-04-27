<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_type', 20)->nullable();
            $table->integer('staff_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('create_staff_id')->nullable();
            $table->integer('create_student_id')->nullable();
            $table->integer('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};