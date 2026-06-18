<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('permission_group_id')->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('menu', 500)->nullable();
            $table->string('activate_menu', 100)->nullable();
            $table->string('lang_key', 250);
            $table->integer('system_level')->nullable()->default(0);
            $table->integer('level')->nullable();
            $table->integer('sidebar_display')->nullable()->default(0);
            $table->text('access_permissions')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_menus');
    }
};
