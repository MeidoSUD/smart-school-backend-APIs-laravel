<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 50);
            $table->string('name', 100);
            $table->string('api_id', 100);
            $table->string('authkey', 100);
            $table->string('senderid', 100);
            $table->text('contact')->nullable();
            $table->string('username', 150)->nullable();
            $table->string('url', 150)->nullable();
            $table->string('password', 150)->nullable();
            $table->string('is_active', 255)->nullable()->default('disabled');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_config');
    }
};
