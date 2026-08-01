<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('class_id')->unsigned();
            $table->integer('section_id')->unsigned();
            $table->integer('is_active')->default(1);

            $table->timestamps();

            $table->index('class_id');
            $table->index('section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};
