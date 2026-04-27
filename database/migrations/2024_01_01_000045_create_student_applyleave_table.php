<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_applyleave', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_session_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->date('apply_date');
            $table->tinyInteger('status');
            $table->string('docs', 200)->nullable();
            $table->text('reason');
            $table->unsignedBigInteger('approve_by')->nullable();
            $table->date('approve_date')->nullable();
            $table->integer('request_type')->comment('0 student,1 staff');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_applyleave');
    }
};