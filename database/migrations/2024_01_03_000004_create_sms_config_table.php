<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_config', function (Blueprint $table) {
            $table->id();
            $table->string('sms_config_name', 255);
            $table->string('sms_service', 255);
            $table->string('api_id', 255);
            $table->string('api_key', 255);
            $table->string('auth_token', 255);
            $table->string('senderid', 255);
            $table->text('contact');
            $table->enum('is_active', ['no', 'yes'])->default('yes');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sms_config` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sms_config` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_config');
    }
};
