<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exp_head_id')->nullable();
            $table->string('name', 50)->nullable();
            $table->string('invoice_no', 200)->nullable();
            $table->date('date')->nullable();
            $table->float('amount')->nullable();
            $table->string('documents', 255)->nullable();
            $table->text('note')->nullable();
            $table->string('is_active', 255)->default('yes');
            $table->string('is_deleted', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};