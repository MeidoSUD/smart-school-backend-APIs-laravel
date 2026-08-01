<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('read_notification', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('send_notification_id');
            $table->integer('staff_id');
            $table->integer('student_id');

            $table->timestamps();

            $table->index('send_notification_id');
            $table->index('staff_id');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('read_notification');
    }
};
