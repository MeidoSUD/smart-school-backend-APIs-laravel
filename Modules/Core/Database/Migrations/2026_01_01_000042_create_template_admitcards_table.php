<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_admitcards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('template', 250)->nullable();
            $table->text('heading')->nullable();
            $table->text('title')->nullable();
            $table->string('left_logo', 200)->nullable();
            $table->string('right_logo', 200)->nullable();
            $table->string('exam_name', 200)->nullable();
            $table->string('school_name', 200)->nullable();
            $table->string('exam_center', 200)->nullable();
            $table->string('sign', 200)->nullable();
            $table->string('background_img', 200)->nullable();
            $table->integer('is_name')->default(1);
            $table->integer('is_father_name')->default(1);
            $table->integer('is_mother_name')->default(1);
            $table->integer('is_dob')->default(1);
            $table->integer('is_admission_no')->default(1);
            $table->integer('is_roll_no')->default(1);
            $table->integer('is_address')->default(1);
            $table->integer('is_gender')->default(1);
            $table->integer('is_photo');
            $table->integer('is_class')->default(0);
            $table->integer('is_section')->default(0);
            $table->text('content_footer')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_admitcards');
    }
};
