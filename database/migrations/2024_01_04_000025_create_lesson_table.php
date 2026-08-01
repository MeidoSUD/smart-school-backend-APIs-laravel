<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('session_id')->unsigned();
            $table->integer('subject_group_subject_id')->unsigned();
            $table->integer('subject_group_class_section_id')->unsigned();
            $table->string('name', 255);
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('session_id');
            $table->index('subject_group_subject_id');
            $table->index('subject_group_class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson');
    }
};
