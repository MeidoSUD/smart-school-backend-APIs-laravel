<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_houses', function (Blueprint $table) {
            $table->id();
            $table->string('house_name', 100);
            $table->string('house_description', 255);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `school_houses` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `school_houses` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('school_houses');
    }
};
