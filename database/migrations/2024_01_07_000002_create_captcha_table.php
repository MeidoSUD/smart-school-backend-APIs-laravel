<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('captcha', function (Blueprint $table) {
            $table->id();
            $table->string('captcha', 7);
            $table->string('ip_address', 45);
            $table->string('word', 20);
            $table->string('filename', 128);
            $table->integer('expiry');
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('captcha');
    }
};
