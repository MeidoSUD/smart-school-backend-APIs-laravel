<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('student_session_id')->nullable();
            $table->integer('feemaster_id')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->float('amount_discount', 10, 2);
            $table->float('amount_fine', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('payment_mode', 50);
            $table->string('is_active', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
