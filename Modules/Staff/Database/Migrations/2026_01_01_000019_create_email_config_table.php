<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_config', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email_type', 100)->nullable();
            $table->string('smtp_server', 100)->nullable();
            $table->string('smtp_port', 100)->nullable();
            $table->string('smtp_username', 100)->nullable();
            $table->string('smtp_password', 100)->nullable();
            $table->string('ssl_tls', 100)->nullable();
            $table->string('smtp_auth', 10);
            $table->string('api_key', 255)->nullable();
            $table->string('api_secret', 255)->nullable();
            $table->string('region', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_config');
    }
};
