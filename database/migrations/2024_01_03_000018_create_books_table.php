<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->integer('book_no');
            $table->string('book_name', 255);
            $table->string('subject_name', 255);
            $table->string('author', 255);
            $table->string('publisher', 255);
            $table->string('isbn_no', 255);
            $table->integer('price');
            $table->integer('quantity');
            $table->string('rack_no', 50);
            $table->string('book_position', 200);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `books` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `books` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
