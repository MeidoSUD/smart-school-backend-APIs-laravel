<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_students', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('reference_no', 50);
            $table->string('firstname', 100)->nullable();
            $table->string('lastname', 100)->nullable();
            $table->integer('class_section_id')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 100)->nullable();
            $table->integer('category_id')->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('cast', 50)->nullable();
            $table->string('mobileno', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('father_name', 100)->nullable();
            $table->string('father_phone', 100)->nullable();
            $table->string('father_occupation', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_phone', 100)->nullable();
            $table->string('mother_occupation', 100)->nullable();
            $table->string('guardian_is', 100)->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_relation', 100)->nullable();
            $table->string('guardian_phone', 100)->nullable();
            $table->string('guardian_email', 100)->nullable();
            $table->string('guardian_occupation', 150)->nullable();
            $table->text('guardian_address')->nullable();
            $table->integer('school_house_id')->nullable();
            $table->string('blood_group', 200)->nullable();
            $table->string('student_photo', 255)->nullable();
            $table->integer('form_status')->nullable()->default(0);
            $table->integer('paid_status')->nullable()->default(0);
            $table->integer('admission_id')->nullable();
            $table->string('file_birth_certificate', 255)->nullable();
            $table->string('file_aadhar_card', 255)->nullable();
            $table->string('file_school_leaving_certificate', 255)->nullable();
            $table->string('file_character_certificate', 255)->nullable();
            $table->string('file_transfer_certificate', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_students');
    }
};
