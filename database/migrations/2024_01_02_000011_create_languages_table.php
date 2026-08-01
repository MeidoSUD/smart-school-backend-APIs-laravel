<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('language', 100);
            $table->enum('is_rtl', ['0', '1'])->default('0');
            $table->integer('is_default')->default(0);
            $table->enum('is_active', ['0', '1'])->default('1');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `languages` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
