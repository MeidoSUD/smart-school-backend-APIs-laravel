<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apply_leave', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('student_session_id');
            $table->date('apply_date');
            $table->date('from_date');
            $table->date('to_date');
            $table->text('reason');
            $table->integer('status')->nullable()->default(0);
            $table->integer('approve_by')->nullable();
            $table->string('docs', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apply_leave');
    }
};
