<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_upload_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('upload_content_id');
            $table->string('user_type', 50);
            $table->integer('user_id');
            $table->integer('class_section_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('upload_content_id', 'share_upload_contents_upload_content_id_index');
            $table->index('user_id', 'share_upload_contents_user_id_index');
            $table->index('class_section_id', 'share_upload_contents_class_section_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_upload_contents');
    }
};
