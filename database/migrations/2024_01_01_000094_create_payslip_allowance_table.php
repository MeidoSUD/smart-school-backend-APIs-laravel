<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_allowance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payslip_id');
            $table->string('allowance_type', 200);
            $table->float('amount');
            $table->unsignedBigInteger('staff_id');
            $table->string('cal_type', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_allowance');
    }
};