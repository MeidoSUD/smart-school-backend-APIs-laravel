<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 100)->nullable();
            $table->string('is_mail', 10)->nullable()->default(0);
            $table->string('is_sms', 10)->nullable()->default(0);
            $table->integer('is_notification')->default(0);
            $table->integer('display_notification')->default(0);
            $table->integer('display_sms')->default(1);
            $table->integer('is_student_recipient')->nullable();
            $table->integer('is_guardian_recipient')->nullable();
            $table->integer('is_staff_recipient')->nullable();
            $table->integer('display_student_recipient')->nullable();
            $table->integer('display_guardian_recipient')->nullable();
            $table->integer('display_staff_recipient')->nullable();
            $table->string('subject', 255);
            $table->string('template_id', 100);
            $table->longText('template');
            $table->text('variables');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_setting');
    }
};
