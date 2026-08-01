<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('employee_id');
            $table->integer('role_id');
            $table->integer('staff_designation_id');
            $table->integer('department_id');
            $table->string('staff_name', 255);
            $table->string('fathers_name', 255);
            $table->string('mothers_name', 255);
            $table->date('date_of_birth');
            $table->string('cnic_no', 30);
            $table->string('marital_status', 20);
            $table->string('phone', 20);
            $table->string('email', 255)->unique();
            $table->text('address');
            $table->string('gender', 10);
            $table->string('qualification', 255);
            $table->string('work_exp', 255);
            $table->text('note');
            $table->date('date_of_joining');
            $table->date('date_of_leaving');
            $table->decimal('employee_salary', 15, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->boolean('is_login')->default(true);

            $table->timestamps();

            $table->index('role_id');
            $table->index('staff_designation_id');
            $table->index('department_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
