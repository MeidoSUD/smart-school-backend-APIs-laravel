<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('class_id');
            $table->integer('section_id');
            $table->integer('session_id');
            $table->integer('staff_id');
            $table->integer('subject_group_subject_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->date('homework_date');
            $table->date('submit_date');
            $table->float('marks', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('create_date');
            $table->date('evaluation_date')->nullable();
            $table->string('document', 200)->nullable();
            $table->integer('created_by');
            $table->integer('evaluated_by')->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework');
    }
};
