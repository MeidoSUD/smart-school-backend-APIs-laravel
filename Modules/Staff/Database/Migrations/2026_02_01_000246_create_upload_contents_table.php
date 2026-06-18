<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('content_type_id');
            $table->string('image', 300)->nullable();
            $table->string('thumb_path', 300)->nullable();
            $table->string('dir_path', 300)->nullable();
            $table->text('real_name');
            $table->string('img_name', 300)->nullable();
            $table->string('thumb_name', 300)->nullable();
            $table->string('file_type', 100);
            $table->text('mime_type');
            $table->string('file_size', 100);
            $table->text('vid_url');
            $table->string('vid_title', 250);
            $table->integer('upload_by');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_contents');
    }
};
