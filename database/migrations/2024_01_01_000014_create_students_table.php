<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('admission_no', 100)->nullable();
            $table->string('roll_no', 100)->nullable();
            $table->date('admission_date')->nullable();
            $table->string('firstname', 100)->nullable();
            $table->string('middlename', 255)->nullable();
            $table->string('lastname', 100)->nullable();
            $table->string('rte', 20)->nullable();
            $table->string('image', 100)->nullable();
            $table->string('mobileno', 100)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('pincode', 100)->nullable();
            $table->string('religion', 100)->nullable();
            $table->string('cast', 50)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 100)->nullable();
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('category_id', 100)->nullable();
            $table->unsignedBigInteger('school_house_id')->nullable();
            $table->string('blood_group', 200);
            $table->unsignedBigInteger('hostel_room_id')->nullable();
            $table->string('adhar_no', 100)->nullable();
            $table->string('samagra_id', 100)->nullable();
            $table->string('bank_account_no', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('ifsc_code', 100)->nullable();
            $table->string('guardian_is', 100);
            $table->string('father_name', 100)->nullable();
            $table->string('father_phone', 100)->nullable();
            $table->string('father_occupation', 100)->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->string('mother_phone', 100)->nullable();
            $table->string('mother_occupation', 100)->nullable();
            $table->string('guardian_name', 100)->nullable();
            $table->string('guardian_relation', 100)->nullable();
            $table->string('guardian_phone', 100)->nullable();
            $table->string('guardian_occupation', 150);
            $table->text('guardian_address')->nullable();
            $table->string('guardian_email', 100)->nullable();
            $table->string('father_pic', 200);
            $table->string('mother_pic', 200);
            $table->string('guardian_pic', 200);
            $table->string('is_active', 255)->default('yes');
            $table->text('previous_school')->nullable();
            $table->string('height', 100);
            $table->string('weight', 100);
            $table->date('measurement_date')->nullable();
            $table->integer('dis_reason')->default(0);
            $table->text('note')->nullable();
            $table->text('dis_note');
            $table->text('app_key')->nullable();
            $table->text('parent_app_key')->nullable();
            $table->date('disable_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};