<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('permission_group_id')->index();
            $table->integer('menu_id');
            $table->string('menu_name', 100);
            $table->string('icon', 100);
            $table->string('menu_url', 255);
            $table->string('module_name', 255);
            $table->boolean('is_active')->default(1);
            $table->integer('sort_order');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sidebar_menus` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sidebar_menus` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_menus');
    }
};
