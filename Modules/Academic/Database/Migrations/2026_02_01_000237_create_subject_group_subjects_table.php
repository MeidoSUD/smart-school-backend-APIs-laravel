<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subject_group_subjects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('subject_group_id')->nullable();
            $table->integer('session_id')->nullable();
            $table->integer('subject_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_group_subjects');
    }
};
