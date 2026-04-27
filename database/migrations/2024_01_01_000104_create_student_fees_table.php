<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->unsignedBigInteger('feemaster_id')->nullable();
            $table->float('amount')->nullable();
            $table->float('amount_discount');
            $table->float('amount_fine')->default(0.00);
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('payment_mode', 50);
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};