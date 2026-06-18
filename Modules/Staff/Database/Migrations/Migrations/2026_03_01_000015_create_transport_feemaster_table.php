<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_feemaster', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('session_id');

            $table->string('month', 50)->nullable();
            $table->date('due_date')->nullable();
            $table->float('fine_amount', 10, 2)->nullable()->default(0.00);
            $table->string('fine_type', 50)->nullable();
            $table->float('fine_percentage', 10, 2)->nullable()->default(0.00);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_feemaster');
    }
};
