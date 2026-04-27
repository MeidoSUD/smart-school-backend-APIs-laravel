<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 200);
            $table->integer('lang_id')->default(0);
            $table->integer('currency_id')->default(0);
            $table->integer('department')->nullable();
            $table->integer('designation')->nullable();
            $table->string('qualification', 200);
            $table->string('work_exp', 200);
            $table->string('name', 200);
            $table->string('surname', 200);
            $table->string('father_name', 200);
            $table->string('mother_name', 200);
            $table->string('contact_no', 200);
            $table->string('emergency_contact_no', 200);
            $table->string('email', 200);
            $table->date('dob');
            $table->string('marital_status', 100);
            $table->date('date_of_joining')->nullable();
            $table->date('date_of_leaving')->nullable();
            $table->string('local_address', 300);
            $table->string('permanent_address', 200);
            $table->string('note', 200);
            $table->string('image', 200);
            $table->string('password', 250);
            $table->string('gender', 50);
            $table->string('account_title', 200);
            $table->string('bank_account_no', 200);
            $table->string('bank_name', 200);
            $table->string('ifsc_code', 200);
            $table->string('bank_branch', 100);
            $table->string('payscale', 200);
            $table->integer('basic_salary')->nullable();
            $table->string('epf_no', 200);
            $table->string('contract_type', 100);
            $table->string('shift', 100);
            $table->string('location', 100);
            $table->string('facebook', 200);
            $table->string('twitter', 200);
            $table->string('linkedin', 200);
            $table->string('instagram', 200);
            $table->string('resume', 200);
            $table->string('joining_letter', 200);
            $table->string('resignation_letter', 200);
            $table->string('other_document_name', 200);
            $table->string('other_document_file', 200);
            $table->integer('user_id')->default(0);
            $table->integer('is_active')->default(0);
            $table->string('verification_code', 100);
            $table->date('disable_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};