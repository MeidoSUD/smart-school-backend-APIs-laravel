<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_feemaster', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id');
            $table->string('month', 50)->nullable();
            $table->date('due_date')->nullable();
            $table->float('fine_amount')->default(0.00);
            $table->string('fine_type', 50)->nullable();
            $table->float('fine_percentage')->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_feemaster');
    }
};