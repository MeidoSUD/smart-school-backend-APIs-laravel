<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->default(0);
            $table->string('username', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->text('childs');
            $table->string('role', 30);
            $table->integer('lang_id')->default(0);
            $table->integer('currency_id')->default(0);
            $table->string('verification_code', 200);
            $table->string('is_active', 255)->default('yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};