<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_sub_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('sidebar_menu_id')->index();
            $table->integer('permission_group_id')->index();
            $table->string('menu_name', 255);
            $table->string('menu_url', 255);
            $table->boolean('is_active')->default(1);
            $table->integer('sort_order');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sidebar_sub_menus` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sidebar_sub_menus` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_sub_menus');
    }
};
