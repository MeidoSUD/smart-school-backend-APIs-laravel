<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_payslip', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('staff_id');
            $table->float('basic', 10, 2);
            $table->float('total_allowance', 10, 2);
            $table->float('total_deduction', 10, 2);
            $table->integer('leave_deduction');
            $table->string('tax', 200);
            $table->float('net_salary', 10, 2);
            $table->string('status', 100);
            $table->string('month', 200);
            $table->string('year', 200);
            $table->string('payment_mode', 200);
            $table->date('payment_date');
            $table->string('remark', 200);
            $table->integer('generated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('staff_payslip', function (Blueprint $table) {
            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_payslip');
    }
};
