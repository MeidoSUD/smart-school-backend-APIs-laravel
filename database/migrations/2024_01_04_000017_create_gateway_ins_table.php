<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_ins', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('online_admission_id')->unsigned();
            $table->string('gateway_name', 100);
            $table->string('transaction_id', 255);
            $table->decimal('paid_amount', 15, 2);
            $table->decimal('received_amount', 15, 2);
            $table->text('response');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('online_admission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_ins');
    }
};
