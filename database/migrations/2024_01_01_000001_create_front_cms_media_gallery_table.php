<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_media_gallery', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->enum('media_type', ['youtube', 'vimeo', 'images']);
            $table->text('media_path');
            $table->string('user_type', 50);
            $table->integer('user_id')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_media_gallery');
    }
};
