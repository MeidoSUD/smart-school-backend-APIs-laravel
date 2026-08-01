<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_edit_fields', function (Blueprint $table) {
            $table->id();
            $table->string('student_edit_field', 255);
            $table->boolean('show')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `student_edit_fields` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `student_edit_fields` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('student_edit_fields');
    }
};
