<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submit_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->text('message');
            $table->string('docs', 225);
            $table->text('file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submit_assignment');
    }
};