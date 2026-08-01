<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_groups', function (Blueprint $table) {
            $table->id();
            $table->string('exam_group_name', 255);
            $table->string('exam_type', 50);
            $table->integer('exam_id');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `exam_groups` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `exam_groups` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_groups');
    }
};
