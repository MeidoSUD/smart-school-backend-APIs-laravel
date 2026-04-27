<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_evaluation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->float('marks')->nullable();
            $table->string('note', 255);
            $table->date('date');
            $table->string('status', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_evaluation');
    }
};