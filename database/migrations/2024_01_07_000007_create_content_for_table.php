<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_for', function (Blueprint $table) {
            $table->id();
            $table->integer('content_id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('content_id', 'content_for_content_id_index');
            $table->index('class_id', 'content_for_class_id_index');
            $table->index('section_id', 'content_for_section_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_for');
    }
};
