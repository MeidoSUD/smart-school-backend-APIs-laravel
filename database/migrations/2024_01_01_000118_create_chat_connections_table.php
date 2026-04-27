<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_connections', function (Blueprint $table) {
            $table->id();
            $table->integer('chat_user_one');
            $table->integer('chat_user_two');
            $table->string('ip', 30)->nullable();
            $table->integer('time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_connections');
    }
};