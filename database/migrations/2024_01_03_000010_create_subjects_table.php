<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('subject_name', 100);
            $table->string('subject_code', 100);
            $table->string('subject_type', 100);
            $table->string('subject_group', 100);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `subjects` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `subjects` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
