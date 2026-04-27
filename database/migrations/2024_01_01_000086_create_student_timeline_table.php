<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_timeline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('title', 200);
            $table->date('timeline_date');
            $table->text('description');
            $table->string('document', 200)->nullable();
            $table->string('status', 200);
            $table->integer('created_student_id');
            $table->date('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_timeline');
    }
};