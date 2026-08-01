<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['admin', 'staff', 'student', 'parent'])->index();
            $table->string('role', 20)->index();
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->text('address');
            $table->string('phone_number', 20);
            $table->integer('lang_id')->default(1);
            $table->integer('student_id')->index();
            $table->integer('parent_id')->index();
            $table->integer('staff_id')->index();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `users` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `users` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
