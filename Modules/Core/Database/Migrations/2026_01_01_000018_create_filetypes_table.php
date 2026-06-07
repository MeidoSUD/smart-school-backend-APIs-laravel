<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filetypes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('file_extension')->nullable();
            $table->text('file_mime')->nullable();
            $table->integer('file_size');
            $table->text('image_extension')->nullable();
            $table->text('image_mime')->nullable();
            $table->integer('image_size');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filetypes');
    }
};
