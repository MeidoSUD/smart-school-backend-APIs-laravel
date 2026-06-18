<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('perm_cat_id')->nullable();
            $table->integer('can_view')->nullable();
            $table->integer('can_add')->nullable();
            $table->integer('can_edit')->nullable();
            $table->integer('can_delete')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('role_id');
            $table->index('perm_cat_id');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_permissions');
    }
};
