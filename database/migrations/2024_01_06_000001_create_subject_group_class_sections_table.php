<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_group_class_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('subject_group_id');
            $table->integer('class_section_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('subject_group_id');
            $table->index('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_class_sections');
    }
};
