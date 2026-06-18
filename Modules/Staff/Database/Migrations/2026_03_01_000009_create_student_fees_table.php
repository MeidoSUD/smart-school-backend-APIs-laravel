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
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->unsignedBigInteger('feemaster_id')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->float('amount_discount', 10, 2);
            $table->float('amount_fine', 10, 2)->default(0.00);
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->string('payment_mode', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });

        Schema::table('student_fees', function (Blueprint $table) {
            $table->index('student_session_id');
            $table->index('feemaster_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
