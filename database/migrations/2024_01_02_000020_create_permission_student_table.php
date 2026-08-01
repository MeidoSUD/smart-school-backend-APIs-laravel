<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_student', function (Blueprint $table) {
            $table->id();
            $table->string('permission_student', 255);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `permission_student` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_student');
    }
};
