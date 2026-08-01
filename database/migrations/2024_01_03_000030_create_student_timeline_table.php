<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_timeline', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->index();
            $table->string('title', 255);
            $table->text('description');
            $table->string('file_type', 255);
            $table->string('document', 255);
            $table->date('date');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `student_timeline` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `student_timeline` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('student_timeline');
    }
};
