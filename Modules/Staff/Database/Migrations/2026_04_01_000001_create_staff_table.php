<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('employee_id', 200);
            $table->unsignedBigInteger('lang_id');
            $table->unsignedBigInteger('currency_id')->nullable()->default(0);
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
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_active')->default(true);
            $table->string('verification_code', 100);
            $table->date('disable_at')->nullable();
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('is_active');
            $table->index('email');
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
