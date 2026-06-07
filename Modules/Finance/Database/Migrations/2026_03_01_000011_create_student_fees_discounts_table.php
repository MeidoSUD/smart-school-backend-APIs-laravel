<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('student_session_id')->nullable();
            $table->integer('fees_discount_id')->nullable();
            $table->string('status', 20)->nullable()->default('assigned');
            $table->string('payment_id', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_discounts');
    }
};
