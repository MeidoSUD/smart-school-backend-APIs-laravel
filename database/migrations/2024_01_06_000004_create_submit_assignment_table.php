<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submit_assignment', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('student_id');
            $table->integer('assignment_id');
            $table->string('assignment_file', 255);
            $table->string('remarks', 255);
            $table->integer('marks');
            $table->enum('status', ['submitted', 'evaluated', 'rejected'])->default('submitted');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('student_id');
            $table->index('assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submit_assignment');
    }
};
