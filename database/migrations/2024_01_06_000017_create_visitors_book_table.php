<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors_book', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('staff_id');
            $table->integer('student_session_id');
            $table->string('visitors_name', 255);
            $table->string('visitors_phone', 20);
            $table->string('visitors_email', 100);
            $table->string('id_proof', 255);
            $table->integer('no_of_person');
            $table->string('visitors_purpose', 100);
            $table->string('meeting_to', 100);
            $table->time('in_time');
            $table->time('out_time');
            $table->text('note');
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('staff_id');
            $table->index('student_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors_book');
    }
};
