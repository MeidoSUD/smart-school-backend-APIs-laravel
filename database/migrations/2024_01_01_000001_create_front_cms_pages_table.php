<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 255)->index('front_cms_pages_slug_index');
            $table->string('title', 255);
            $table->text('description');
            $table->string('path', 255)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('user_type', 50);
            $table->integer('user_id')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_pages');
    }
};
