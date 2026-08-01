<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendence_type', function (Blueprint $table) {
            $table->id();
            $table->string('attendence_type', 100);
            $table->string('key_value', 10);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `attendence_type` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `attendence_type` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('attendence_type');
    }
};
