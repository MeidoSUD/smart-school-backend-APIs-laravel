<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('staff_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('is_active')->default(1);

            $table->timestamps();

            $table->index('staff_id');
            $table->index('student_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_users');
    }
};
