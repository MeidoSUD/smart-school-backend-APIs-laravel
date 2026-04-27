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
            $table->string('book_title', 100);
            $table->string('book_no', 50);
            $table->string('isbn_no', 100);
            $table->string('subject', 100)->nullable();
            $table->string('rack_no', 100);
            $table->string('publish', 100)->nullable();
            $table->string('author', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->float('perunitcost')->nullable();
            $table->date('postdate')->nullable();
            $table->text('description')->nullable();
            $table->string('available', 10)->default('yes');
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};