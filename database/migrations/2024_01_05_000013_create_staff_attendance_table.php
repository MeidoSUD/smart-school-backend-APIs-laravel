<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('staff_attendance_type_id');
            $table->date('attendance_date');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('staff_attendance_type_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
