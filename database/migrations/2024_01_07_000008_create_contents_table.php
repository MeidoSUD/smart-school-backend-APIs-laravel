<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id');
            $table->string('title', 255);
            $table->text('description');
            $table->string('content_type', 255);
            $table->string('upload_content', 255);
            $table->timestamps();
            $table->index('staff_id', 'contents_staff_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
