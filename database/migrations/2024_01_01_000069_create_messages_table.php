<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200)->nullable();
            $table->string('template_id', 100)->nullable();
            $table->integer('email_template_id')->nullable();
            $table->integer('sms_template_id')->nullable();
            $table->string('send_through', 20)->nullable();
            $table->text('message')->nullable();
            $table->string('send_mail', 10)->default('0');
            $table->string('send_sms', 10)->default('0');
            $table->string('is_group', 10)->default('0');
            $table->string('is_individual', 10)->default('0');
            $table->integer('is_class')->default(0);
            $table->tinyInteger('is_schedule');
            $table->integer('sent')->nullable();
            $table->dateTime('schedule_date_time')->nullable();
            $table->text('group_list')->nullable();
            $table->text('user_list')->nullable();
            $table->integer('schedule_class')->nullable();
            $table->string('schedule_section', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};