<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applyleave', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('student_session_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->string('leave_day', 10);
            $table->text('reason');
            $table->enum('status', ['P', 'A', 'L'])->default('P');
            $table->integer('approve_by');
            $table->string('document', 255);

            $table->timestamps();

            $table->index('student_session_id');
            $table->index('approve_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_applyleave');
    }
};
