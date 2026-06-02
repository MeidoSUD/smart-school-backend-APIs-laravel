<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_menu_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('menu_id');
            $table->string('menu', 100)->nullable();
            $table->integer('page_id');
            $table->integer('parent_id');
            $table->text('ext_url')->nullable();
            $table->integer('open_new_tab')->nullable()->default(0);
            $table->text('ext_url_link')->nullable();
            $table->string('slug', 200)->nullable();
            $table->integer('weight')->nullable();
            $table->integer('publish')->default(0);
            $table->text('description')->nullable();
            $table->string('is_active', 10)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_menu_items');
    }
};
