<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sidebar_sub_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sidebar_menu_id')->nullable();
            $table->string('menu', 500)->nullable();
            $table->string('key', 500)->nullable();
            $table->string('lang_key', 250)->nullable();
            $table->text('url')->nullable();
            $table->integer('level')->nullable();
            $table->string('access_permissions', 500)->nullable();
            $table->unsignedBigInteger('permission_group_id')->nullable();
            $table->string('activate_controller', 100)->nullable()->comment('income');
            $table->string('activate_methods', 500)->nullable()->comment('index,edit');
            $table->string('addon_permission', 100)->nullable();
            $table->integer('is_active')->nullable()->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sidebar_sub_menus');
    }
};
