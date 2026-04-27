<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_master', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('is_system')->default(0);
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->integer('fee_session_group_id')->nullable();
            $table->float('amount')->default(0.00);
            $table->string('is_active', 10)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_master');
    }
};