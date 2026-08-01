<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_attempts', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('onlineexam_id')->unsigned();
            $table->integer('student_id')->unsigned();
            $table->integer('attempt_no');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('onlineexam_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_attempts');
    }
};
