<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('session_id');
            $table->integer('subject_group_subject_id');
            $table->integer('subject_group_class_sections_id');
            $table->string('name', 255);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson');
    }
};
