<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('exam_group_id')->nullable();
            $table->integer('student_id')->nullable();
            $table->integer('student_session_id')->nullable();
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_students');
    }
};
