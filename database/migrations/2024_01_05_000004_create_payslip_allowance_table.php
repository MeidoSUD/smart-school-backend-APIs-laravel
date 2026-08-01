<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_allowance', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('staff_payslip_id');
            $table->integer('payscale_allowance_id');
            $table->decimal('amount', 15, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('staff_payslip_id');
            $table->index('payscale_allowance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_allowance');
    }
};
