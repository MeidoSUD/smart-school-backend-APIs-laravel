<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_exam_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('exam_group_id')->nullable();
            $table->integer('exam_group_class_batch_exams_id')->nullable();
            $table->float('exam_weightage', 10, 2)->nullable()->default(0.00);
            $table->integer('is_active')->nullable()->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_exam_connections');
    }
};
