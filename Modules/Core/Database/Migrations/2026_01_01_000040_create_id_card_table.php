<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100);
            $table->string('school_name', 100);
            $table->string('school_address', 500);
            $table->string('background', 100);
            $table->string('logo', 100);
            $table->string('sign_image', 100);
            $table->integer('enable_vertical_card')->default(0);
            $table->string('header_color', 100);
            $table->boolean('enable_admission_no')->comment('0=disable,1=enable');
            $table->boolean('enable_student_name')->comment('0=disable,1=enable');
            $table->boolean('enable_class')->comment('0=disable,1=enable');
            $table->boolean('enable_fathers_name')->comment('0=disable,1=enable');
            $table->boolean('enable_mothers_name')->comment('0=disable,1=enable');
            $table->boolean('enable_address')->comment('0=disable,1=enable');
            $table->boolean('enable_phone')->comment('0=disable,1=enable');
            $table->boolean('enable_dob')->comment('0=disable,1=enable');
            $table->boolean('enable_blood_group')->comment('0=disable,1=enable');
            $table->tinyInteger('enable_student_barcode')->default(1)->comment('0=disable,1=enable');
            $table->boolean('status')->comment('0=disable,1=enable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card');
    }
};
