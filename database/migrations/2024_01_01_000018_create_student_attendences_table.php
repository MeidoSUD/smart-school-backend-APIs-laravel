<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->tinyInteger('biometric_attendence')->default(0);
            $table->tinyInteger('qrcode_attendance')->default(0);
            $table->date('date')->nullable();
            $table->unsignedBigInteger('attendence_type_id')->nullable();
            $table->string('remark', 200);
            $table->text('biometric_device_data')->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendences');
    }
};