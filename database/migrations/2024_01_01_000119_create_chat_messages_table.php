<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->text('message')->nullable();
            $table->integer('chat_user_id');
            $table->string('ip', 30);
            $table->integer('time');
            $table->tinyInteger('is_first')->default(0);
            $table->tinyInteger('is_read')->default(0);
            $table->integer('chat_connection_id');
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};