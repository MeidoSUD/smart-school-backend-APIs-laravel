<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_content_for', function (Blueprint $table) {
            $table->id();
            $table->string('group_id', 20)->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('user_parent_id')->nullable();
            $table->integer('staff_id')->nullable();
            $table->integer('class_section_id')->nullable();
            $table->integer('share_content_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_content_for');
    }
};