<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('session_id');
            $table->integer('lesson_id');
            $table->string('name', 255);
            $table->integer('status');
            $table->date('complete_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic');
    }
};
