<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->integer('staff_id');
            $table->integer('staff_attendance_type_id');
            $table->integer('biometric_attendence')->nullable()->default(0);
            $table->integer('qrcode_attendance')->default(0);
            $table->text('biometric_device_data')->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->string('remark', 200);
            $table->integer('is_active');
            $table->dateTime('created_at');
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
