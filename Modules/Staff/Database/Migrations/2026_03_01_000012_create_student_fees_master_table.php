<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees_master', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('is_system')->default(0);
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->unsignedBigInteger('fee_session_group_id')->nullable();
            $table->float('amount', 10, 2)->nullable()->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::table('student_fees_master', function (Blueprint $table) {
            $table->index('student_session_id');
            $table->index('fee_session_group_id');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees_master');
    }
};
