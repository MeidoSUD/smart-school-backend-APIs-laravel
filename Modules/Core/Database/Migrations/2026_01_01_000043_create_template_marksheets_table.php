<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_marksheets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('header_image', 200)->nullable();
            $table->string('template', 200)->nullable();
            $table->text('heading')->nullable();
            $table->text('title')->nullable();
            $table->string('left_logo', 200)->nullable();
            $table->string('right_logo', 200)->nullable();
            $table->string('exam_name', 200)->nullable();
            $table->string('school_name', 200)->nullable();
            $table->string('exam_center', 200)->nullable();
            $table->string('left_sign', 200)->nullable();
            $table->string('middle_sign', 200)->nullable();
            $table->string('right_sign', 200)->nullable();
            $table->integer('exam_session')->nullable()->default(1);
            $table->integer('is_name')->nullable()->default(1);
            $table->integer('is_father_name')->nullable()->default(1);
            $table->integer('is_mother_name')->nullable()->default(1);
            $table->integer('is_dob')->nullable()->default(1);
            $table->integer('is_admission_no')->nullable()->default(1);
            $table->integer('is_roll_no')->nullable()->default(1);
            $table->integer('is_photo')->nullable()->default(1);
            $table->integer('is_division')->default(1);
            $table->integer('is_rank')->default(0);
            $table->integer('is_customfield');
            $table->string('background_img', 200)->nullable();
            $table->string('date', 20)->nullable();
            $table->integer('is_class')->default(0);
            $table->integer('is_teacher_remark')->default(1);
            $table->integer('is_section')->default(0);
            $table->text('content')->nullable();
            $table->text('content_footer')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_marksheets');
    }
};
