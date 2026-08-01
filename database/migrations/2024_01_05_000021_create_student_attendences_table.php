<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_attendences', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('attendence_type_id');
            $table->integer('student_session_id');
            $table->date('attendence_date');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('attendence_type_id');
            $table->index('student_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attendences');
    }
};
