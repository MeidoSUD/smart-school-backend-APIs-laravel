<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->boolean('is_active')->default(1);
            $table->boolean('is_superadmin')->default(0);
            $table->boolean('is_staff')->default(0);
            $table->boolean('is_student')->default(0);
            $table->boolean('is_parent')->default(0);
            $table->boolean('is_admin')->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `roles` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
