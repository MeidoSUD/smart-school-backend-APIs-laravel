<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_programs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50)->nullable();
            $table->string('slug', 255)->nullable();
            $table->text('url')->nullable();
            $table->string('title', 200)->nullable();
            $table->date('date')->nullable();
            $table->date('event_start')->nullable();
            $table->date('event_end')->nullable();
            $table->text('event_venue')->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 10)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->text('meta_title');
            $table->text('meta_description');
            $table->text('meta_keyword');
            $table->text('feature_image');
            $table->date('publish_date')->nullable();
            $table->string('publish', 10)->nullable()->default(0);
            $table->integer('sidebar')->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_programs');
    }
};
