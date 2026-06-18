<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_payroll', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('basic_salary');
            $table->string('pay_scale', 200);
            $table->string('grade', 50);
            $table->boolean('is_active')->default(true);
        });

        Schema::table('staff_payroll', function (Blueprint $table) {
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payroll');
    }
};
