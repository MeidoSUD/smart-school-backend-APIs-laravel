<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('send_notification', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('send_notification_title', 255);
            $table->text('send_notification_message');
            $table->date('send_notification_date');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('send_notification');
    }
};
