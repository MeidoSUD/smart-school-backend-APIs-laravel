<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_payslip', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->float('basic');
            $table->float('total_allowance');
            $table->float('total_deduction');
            $table->integer('leave_deduction');
            $table->string('tax', 200);
            $table->float('net_salary');
            $table->string('status', 100);
            $table->string('month', 200);
            $table->string('year', 200);
            $table->string('payment_mode', 200);
            $table->date('payment_date');
            $table->string('remark', 200);
            $table->integer('generated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payslip');
    }
};