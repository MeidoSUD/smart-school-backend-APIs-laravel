<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_name', 100);
            $table->string('section_code', 50);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sections` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sections` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
