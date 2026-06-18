<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offline_fees_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_id', 50)->nullable();
$table->unsignedBigInteger('student_session_id')->nullable();

$table->unsignedBigInteger('student_fees_master_id')->nullable();

$table->unsignedBigInteger('fee_groups_feetype_id')->nullable();

$table->unsignedBigInteger('student_transport_fee_id')->nullable();

            $table->date('payment_date')->nullable();
            $table->string('bank_from', 200)->nullable();
            $table->string('bank_account_transferred', 200)->nullable();
            $table->string('reference', 200)->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->dateTime('submit_date')->nullable();
            $table->dateTime('approve_date')->nullable();
            $table->text('attachment')->nullable();
            $table->text('reply')->nullable();
            $table->integer('approved_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('offline_fees_payments', function (Blueprint $table) {
            $table->index('student_session_id');
            $table->index('student_fees_master_id');
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offline_fees_payments');
    }
};
