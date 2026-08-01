<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_leave_request', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('leave_type_id');
            $table->date('leave_from');
            $table->date('leave_to');
            $table->string('leave_day', 10);
            $table->integer('applied_by');
            $table->text('reason');
            $table->boolean('is_approved')->default(false);
            $table->integer('approved_by');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('leave_type_id');
            $table->index('applied_by');
            $table->index('approved_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_leave_request');
    }
};
