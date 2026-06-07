<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_student', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->nullable();
            $table->string('short_code', 100);
            $table->integer('system');
            $table->integer('student');
            $table->integer('parent');
            $table->integer('group_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_student');
    }
};
