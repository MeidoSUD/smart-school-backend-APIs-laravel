<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_menus', function (Blueprint $table) {
            $table->id();
            $table->string('menu_type', 50);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `front_cms_menus` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `front_cms_menus` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_menus');
    }
};
