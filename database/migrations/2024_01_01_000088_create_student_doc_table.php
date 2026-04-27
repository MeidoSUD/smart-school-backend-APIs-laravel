<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_doc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('title', 200)->nullable();
            $table->string('doc', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_doc');
    }
};