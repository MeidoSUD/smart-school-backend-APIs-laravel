<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_ins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('online_admission_id')->nullable();
            $table->string('gateway_name', 50);
            $table->string('module_type', 255);
            $table->string('unique_id', 255);
            $table->mediumText('parameter_details');
            $table->string('payment_status', 255);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_ins');
    }
};
