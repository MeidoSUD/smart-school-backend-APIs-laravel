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
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('staff_attendance_type_id');
            $table->integer('biometric_attendence')->nullable()->default(0);
            $table->integer('qrcode_attendance')->default(0);
            $table->text('biometric_device_data')->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->string('remark', 200);
            $table->integer('is_active');
            $table->dateTime('created_at');
            $table->date('updated_at')->nullable();
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->index('staff_id');
            $table->index('staff_attendance_type_id');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
