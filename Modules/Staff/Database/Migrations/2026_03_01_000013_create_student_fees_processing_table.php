<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_processing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('gateway_ins_id');
            $table->string('fee_category', 255);
            $table->unsignedBigInteger('student_fees_master_id')->nullable();
            $table->unsignedBigInteger('fee_groups_feetype_id')->nullable();
            $table->unsignedBigInteger('student_transport_fee_id')->nullable();
            $table->text('amount_detail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_processing');
    }
};
