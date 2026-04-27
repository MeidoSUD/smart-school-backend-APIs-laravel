<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('subject_group_subject_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->date('homework_date');
            $table->date('submit_date');
            $table->float('marks')->nullable();
            $table->text('description')->nullable();
            $table->date('create_date');
            $table->date('evaluation_date')->nullable();
            $table->string('document', 200)->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('evaluated_by')->nullable();
            $table->dateTime('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};