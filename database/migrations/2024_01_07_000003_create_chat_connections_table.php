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
            $table->integer('user_one');
            $table->integer('user_two');
            $table->string('ip_address', 45);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_delete')->default(false);
            $table->timestamps();
            $table->index('user_one', 'chat_connections_user_one_index');
            $table->index('user_two', 'chat_connections_user_two_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_connections');
    }
};
