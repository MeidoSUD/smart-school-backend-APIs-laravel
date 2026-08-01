<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_config', function (Blueprint $table) {
            $table->id();
            $table->string('email_config_name', 255);
            $table->string('email_host', 255);
            $table->integer('email_port');
            $table->string('email_prefix', 100);
            $table->string('email_username', 255);
            $table->text('email_password');
            $table->string('email_encryption', 255);
            $table->string('email_fromaddress', 255);
            $table->enum('is_active', ['no', 'yes'])->default('yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_config');
    }
};
