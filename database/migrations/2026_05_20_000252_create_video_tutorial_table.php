<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_tutorial', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100);
            $table->text('vid_title')->nullable();
            $table->text('description');
            $table->string('thumb_path', 500)->nullable();
            $table->string('dir_path', 500)->nullable();
            $table->string('img_name', 300);
            $table->string('thumb_name', 300);
            $table->string('video_link', 100);
            $table->integer('created_by');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_tutorial');
    }
};
