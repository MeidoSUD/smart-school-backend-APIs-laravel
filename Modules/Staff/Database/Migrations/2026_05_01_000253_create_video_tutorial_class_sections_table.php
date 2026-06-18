<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_tutorial_class_sections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('video_tutorial_id');
            $table->unsignedBigInteger('class_section_id');
            $table->date('created_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_tutorial_class_sections');
    }
};
