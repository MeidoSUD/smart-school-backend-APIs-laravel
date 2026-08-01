<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('send_notification_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->integer('is_active')->default(1);

            $table->timestamps();

            $table->index('send_notification_id');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_roles');
    }
};
