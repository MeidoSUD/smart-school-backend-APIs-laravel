<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admissions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('admission_no');
            $table->string('student_name', 100);
            $table->string('uid_no', 30);
            $table->string('mobileno', 15);
            $table->string('email', 100);
            $table->date('dob');
            $table->string('gender', 10);
            $table->integer('category_id')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('route_id')->unsigned();
            $table->integer('hostel_room_id')->unsigned();
            $table->integer('house_id')->unsigned();
            $table->date('admission_date');
            $table->string('status', 15);
            $table->integer('user_id')->unsigned();

            $table->timestamps();

            $table->index('category_id');
            $table->index('class_id');
            $table->index('route_id');
            $table->index('hostel_room_id');
            $table->index('house_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admissions');
    }
};
