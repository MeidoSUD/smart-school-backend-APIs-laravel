<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_id_card', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 255);
            $table->string('school_name', 255);
            $table->string('school_address', 255);
            $table->string('background', 100);
            $table->string('logo', 100);
            $table->string('sign_image', 100);
            $table->string('header_color', 100);
            $table->integer('enable_vertical_card')->default(0);
            $table->boolean('enable_staff_role')->comment('0=disable,1=enable');
            $table->boolean('enable_staff_id')->comment('0=disable,1=enable');
            $table->boolean('enable_staff_department')->comment('0=disable,1=enable');
            $table->boolean('enable_designation')->comment('0=disable,1=enable');
            $table->boolean('enable_name')->comment('0=disable,1=enable');
            $table->boolean('enable_fathers_name')->comment('0=disable,1=enable');
            $table->boolean('enable_mothers_name')->comment('0=disable,1=enable');
            $table->boolean('enable_date_of_joining')->comment('0=disable,1=enable');
            $table->boolean('enable_permanent_address')->comment('0=disable,1=enable');
            $table->boolean('enable_staff_dob')->comment('0=disable,1=enable');
            $table->boolean('enable_staff_phone')->comment('0=disable,1=enable');
            $table->boolean('enable_staff_barcode')->comment('0=disable,1=enable');
            $table->boolean('status')->comment('0=disable,1=enable');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_id_card');
    }
};
