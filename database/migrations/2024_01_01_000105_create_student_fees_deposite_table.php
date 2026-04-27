<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_deposite', function (Blueprint $table) {
            $table->id();
            $table->integer('student_fees_master_id')->nullable();
            $table->integer('fee_groups_feetype_id')->nullable();
            $table->integer('student_transport_fee_id')->nullable();
            $table->text('amount_detail');
            $table->string('is_active', 10)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_deposite');
    }
};