<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles_permissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('role_id');
            $table->integer('permission_category_id');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('role_id');
            $table->index('permission_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles_permissions');
    }
};
