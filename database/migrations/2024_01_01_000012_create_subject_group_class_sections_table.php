<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_group_class_sections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_group_id')->nullable();
            $table->unsignedBigInteger('class_section_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->text('description')->nullable();
            $table->tinyInteger('is_active')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_class_sections');
    }
};