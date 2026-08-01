<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_section_times', function (Blueprint $table) {
            $table->id();
            $table->integer('class_section_id');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('class_section_id', 'class_section_times_class_section_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_section_times');
    }
};
