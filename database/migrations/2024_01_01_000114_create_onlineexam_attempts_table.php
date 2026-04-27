<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_attempts', function (Blueprint $table) {
            $table->id();
            $table->integer('onlineexam_student_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_attempts');
    }
};