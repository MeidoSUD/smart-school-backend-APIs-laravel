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
            $table->unsignedBigInteger('fee_session_group_id')->nullable();
            $table->unsignedBigInteger('fee_groups_id')->nullable();
            $table->unsignedBigInteger('feetype_id')->nullable();
            $table->unsignedBigInteger('session_id')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('fine_type', 50)->default('none');
            $table->date('due_date')->nullable();
            $table->float('fine_percentage', 10, 2)->default(0.00);
            $table->float('fine_amount', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('fee_groups_feetype', function (Blueprint $table) {
            $table->index('fee_groups_id');
            $table->index('feetype_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_groups_feetype');
    }
};
