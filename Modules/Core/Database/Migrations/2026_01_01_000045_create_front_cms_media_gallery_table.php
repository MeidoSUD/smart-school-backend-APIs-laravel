<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_media_gallery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('image', 300)->nullable();
            $table->string('thumb_path', 300)->nullable();
            $table->string('dir_path', 300)->nullable();
            $table->string('img_name', 300)->nullable();
            $table->string('thumb_name', 300)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->string('file_type', 100);
            $table->string('file_size', 100);
            $table->text('vid_url');
            $table->string('vid_title', 250);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_media_gallery');
    }
};
