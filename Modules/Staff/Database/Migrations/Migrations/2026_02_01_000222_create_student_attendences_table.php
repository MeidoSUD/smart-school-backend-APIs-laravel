<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendences', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('student_session_id')->nullable();

            $table->integer('biometric_attendence')->default(0);
            $table->integer('qrcode_attendance')->default(0);
            $table->date('date')->nullable();
            $table->index('date');
$table->unsignedBigInteger('attendence_type_id')->nullable();

            $table->string('remark', 200);
            $table->text('biometric_device_data')->nullable();
            $table->string('user_agent', 250)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendences');
    }
};
