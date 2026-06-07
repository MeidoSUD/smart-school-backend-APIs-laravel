<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_payment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('online_admission_id');
            $table->float('paid_amount', 10, 2);
            $table->string('payment_mode', 50);
            $table->string('payment_type', 100);
            $table->string('transaction_id', 100);
            $table->string('note', 100);
            $table->dateTime('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_payment');
    }
};
