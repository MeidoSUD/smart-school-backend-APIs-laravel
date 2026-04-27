<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->string('is_public', 10)->default('No');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->integer('cls_sec_id')->nullable();
            $table->string('file', 250)->nullable();
            $table->date('date');
            $table->text('note')->nullable();
            $table->string('is_active', 255)->default('no');
            $table->integer('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};