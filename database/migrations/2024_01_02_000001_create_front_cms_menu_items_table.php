<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_menu_items', function (Blueprint $table) {
            $table->id();
            $table->integer('page_id');
            $table->integer('menu_id');
            $table->integer('parent_id');
            $table->string('type', 50);
            $table->string('label', 255);
            $table->string('url', 255);
            $table->string('target', 50);
            $table->integer('sort');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `front_cms_menu_items` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_menu_items');
    }
};
