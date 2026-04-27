<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onlineexam_student_results', function (Blueprint $table) {
            $table->id();
            $table->integer('onlineexam_student_id');
            $table->integer('onlineexam_question_id');
            $table->longText('select_option')->nullable();
            $table->float('marks')->default(0.00);
            $table->text('remark')->nullable();
            $table->text('attachment_name')->nullable();
            $table->string('attachment_upload_name', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onlineexam_student_results');
    }
};