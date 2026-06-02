<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_groups_feetype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fee_session_group_id')->nullable();
            $table->integer('fee_groups_id')->nullable();
            $table->integer('feetype_id')->nullable();
            $table->integer('session_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('fine_type', 50)->default('none');
            $table->date('due_date')->nullable();
            $table->float('fine_percentage', 10, 2)->default(0.00);
            $table->float('fine_amount', 10, 2)->default(0.00);
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_groups_feetype');
    }
};
