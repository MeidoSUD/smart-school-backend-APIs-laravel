<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_assignment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id');
            $table->unsignedBigInteger('subject_group_subject_id');
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->integer('evaluated_by')->nullable();
            $table->date('date')->nullable();
            $table->date('evaluation_date')->nullable();
            $table->text('remark');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_assignment');
    }
};