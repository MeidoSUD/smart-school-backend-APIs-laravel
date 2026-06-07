<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('menu', 100)->nullable();
            $table->string('slug', 200)->nullable();
            $table->text('description')->nullable();
            $table->integer('open_new_tab')->default(0);
            $table->text('ext_url');
            $table->text('ext_url_link');
            $table->integer('publish')->default(0);
            $table->string('content_type', 10)->default('manual');
            $table->string('is_active', 10)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_menus');
    }
};
