<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('subject_group_subject_id');
            $table->unsignedBigInteger('subject_group_class_sections_id');
            $table->string('name', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson');
    }
};