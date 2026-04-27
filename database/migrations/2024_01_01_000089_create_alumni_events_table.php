<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_events', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('event_for', 100);
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('section', 255);
            $table->dateTime('from_date');
            $table->dateTime('to_date');
            $table->text('note');
            $table->string('photo', 255)->nullable();
            $table->integer('is_active');
            $table->text('event_notification_message');
            $table->integer('show_onwebsite');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_events');
    }
};