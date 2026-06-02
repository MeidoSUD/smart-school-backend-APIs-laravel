<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submit_assignment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('homework_id');
            $table->integer('student_id');
            $table->text('message');
            $table->string('docs', 225);
            $table->text('file_name')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submit_assignment');
    }
};
