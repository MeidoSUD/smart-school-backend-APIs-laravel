<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('message')->nullable();
            $table->text('record_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('action', 50)->nullable();
            $table->string('ip_address', 50)->nullable();
            $table->string('platform', 50)->nullable();
            $table->string('agent', 50)->nullable();
            $table->timestamp('time')->useCurrent();
            $table->date('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
