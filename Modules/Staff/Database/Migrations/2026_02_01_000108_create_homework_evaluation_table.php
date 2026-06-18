<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_evaluation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->index('student_id');
            $table->unsignedBigInteger('student_session_id')->nullable();
            $table->index('student_session_id');
            $table->float('marks', 10, 2)->nullable();
            $table->string('note', 255);
            $table->date('date');
            $table->string('status', 100);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_evaluation');
    }
};
