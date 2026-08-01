<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_category', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('permission_group_id');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('permission_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_category');
    }
};
