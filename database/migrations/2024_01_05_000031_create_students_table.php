<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('admission_no', 20)->unique();
            $table->date('admission_date');
            $table->string('student_photo', 255);
            $table->integer('roll_no');
            $table->string('First_name', 100);
            $table->string('Last_name', 100);
            $table->string('Father_name', 100);
            $table->string('Father_phone', 20);
            $table->string('Father_occupation', 100);
            $table->string('Mother_name', 100);
            $table->string('Mother_phone', 20);
            $table->string('Mother_occupation', 100);
            $table->string('Guardian_name', 100);
            $table->string('Guardian_phone', 20);
            $table->string('Guardian_occupation', 100);
            $table->string('Guardian_relation', 50);
            $table->text('Guardian_address');
            $table->string('student_email', 255);
            $table->string('student_phone', 20);
            $table->string('student_gender', 10);
            $table->date('dob');
            $table->integer('category_id');
            $table->integer('school_house_id');
            $table->string('blood_group', 20);
            $table->string('religion', 50);
            $table->enum('if_guardian_is', ['father', 'mother', 'other'])->default('father');
            $table->boolean('is_bank_detail')->default(false);
            $table->string('bank_name', 100);
            $table->string('bank_account_no', 50);
            $table->string('bank_code', 50);
            $table->string('bank_branch', 100);
            $table->text('student_address');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->boolean('is_login')->default(true);
            $table->integer('parent_id');
            $table->integer('user_id');
            $table->string('disable_reason', 255);
            $table->enum('student_status', ['active', 'inactive', 'graduated'])->default('active');

            $table->timestamps();

            $table->index('category_id');
            $table->index('school_house_id');
            $table->index('parent_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
