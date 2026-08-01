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
            $table->integer('connection_id');
            $table->integer('user_id');
            $table->string('ip_address', 45);
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->index('connection_id', 'chat_messages_connection_id_index');
            $table->index('user_id', 'chat_messages_user_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
