<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_pages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('page_type', 10)->default('manual');
            $table->integer('is_homepage')->nullable()->default(0);
            $table->string('title', 250)->nullable();
            $table->string('url', 250)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('slug', 200)->nullable();
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keyword')->nullable();
            $table->string('feature_image', 200);
            $table->longText('description')->nullable();
            $table->date('publish_date')->nullable();
            $table->integer('publish')->nullable()->default(0);
            $table->integer('sidebar')->nullable()->default(0);
            $table->string('is_active', 10)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_pages');
    }
};
