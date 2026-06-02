<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_teacher', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('session_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_teacher');
    }
};
