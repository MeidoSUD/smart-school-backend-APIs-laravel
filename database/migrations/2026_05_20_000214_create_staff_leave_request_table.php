<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leave_request', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('staff_id');
            $table->integer('leave_type_id');
            $table->date('leave_from');
            $table->date('leave_to');
            $table->integer('leave_days');
            $table->string('employee_remark', 200);
            $table->string('admin_remark', 200);
            $table->string('status', 50);
            $table->integer('applied_by')->nullable();
            $table->string('document_file', 200);
            $table->date('date');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leave_request');
    }
};
