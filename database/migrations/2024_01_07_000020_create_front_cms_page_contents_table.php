<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_page_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('page_id');
            $table->string('content_type', 50);
            $table->string('heading', 255);
            $table->text('description');
            $table->string('image', 255);
            $table->string('slug', 255);
            $table->string('type', 50);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
            $table->index('page_id', 'front_cms_page_contents_page_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_page_contents');
    }
};
