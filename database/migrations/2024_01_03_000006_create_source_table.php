<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source', function (Blueprint $table) {
            $table->id();
            $table->string('source_name', 100);
            $table->string('description', 255);
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `source` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `source` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('source');
    }
};
