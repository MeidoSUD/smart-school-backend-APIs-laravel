<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('online_admission_id');
            $table->decimal('paid_amount', 15, 2);
            $table->string('transaction_id', 255);
            $table->string('payment_mode', 50);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('online_admission_id', 'online_admission_payment_online_admission_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_payment');
    }
};
