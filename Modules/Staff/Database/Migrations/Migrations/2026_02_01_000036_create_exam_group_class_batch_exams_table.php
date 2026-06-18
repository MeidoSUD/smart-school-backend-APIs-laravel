<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_group_class_batch_exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exam', 250)->nullable();
            $table->float('passing_percentage', 10, 2)->nullable();
$table->unsignedBigInteger('session_id');

            $table->index('session_id');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
$table->unsignedBigInteger('exam_group_id')->nullable();

            $table->integer('use_exam_roll_no')->default(1);
            $table->integer('is_publish')->nullable()->default(0);
            $table->integer('is_rank_generated')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group_class_batch_exams');
    }
};
