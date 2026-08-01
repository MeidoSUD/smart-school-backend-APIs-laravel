<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_attendance_type', function (Blueprint $table) {
            $table->id();
            $table->string('staff_attendance_type', 100);
            $table->string('key_value', 10);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `staff_attendance_type` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `staff_attendance_type` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_attendance_type');
    }
};
