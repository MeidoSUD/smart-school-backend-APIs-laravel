<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id');
            $table->integer('lesson_id');
            $table->string('name', 255);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('session_id', 'topic_session_id_index');
            $table->index('lesson_id', 'topic_lesson_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic');
    }
};
