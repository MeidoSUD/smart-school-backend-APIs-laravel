<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->nullable();
            $table->string('slug', 150)->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->integer('is_system')->default(0);
            $table->integer('is_superadmin')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
