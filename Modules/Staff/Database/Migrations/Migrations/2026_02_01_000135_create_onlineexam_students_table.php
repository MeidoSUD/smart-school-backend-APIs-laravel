<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_students', function (Blueprint $table) {
            $table->bigIncrements('id');
$table->unsignedBigInteger('onlineexam_id')->nullable();

$table->unsignedBigInteger('student_session_id')->nullable();

            $table->integer('is_attempted')->default(0);
            $table->integer('rank')->nullable()->default(0);
            $table->integer('quiz_attempted')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_students');
    }
};
