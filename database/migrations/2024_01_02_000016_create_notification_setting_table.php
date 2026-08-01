<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_setting', function (Blueprint $table) {
            $table->id();
            $table->string('notification_setting', 100);
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->enum('notify_by', ['sms', 'email', 'whatsapp'])->default('email');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `notification_setting` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_setting');
    }
};
