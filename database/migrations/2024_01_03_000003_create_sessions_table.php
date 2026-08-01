<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session', 20);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sessions` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sessions` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
