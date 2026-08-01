<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_doc', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id')->index();
            $table->string('document_title', 255);
            $table->string('document_file', 255);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `student_doc` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `student_doc` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('student_doc');
    }
};
