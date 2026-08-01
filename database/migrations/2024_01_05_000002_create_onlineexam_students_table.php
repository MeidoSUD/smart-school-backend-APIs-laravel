<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_students', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('onlineexam_id');
            $table->integer('student_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('onlineexam_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_students');
    }
};
