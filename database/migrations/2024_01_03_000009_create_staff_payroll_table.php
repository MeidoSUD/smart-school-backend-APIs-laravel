<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_payroll', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->index();
            $table->string('pay_scale', 255);
            $table->string('grade', 255);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `staff_payroll` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `staff_payroll` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payroll');
    }
};
