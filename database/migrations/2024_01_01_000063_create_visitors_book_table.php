<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors_book', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->nullable();
            $table->integer('student_session_id')->nullable();
            $table->string('source', 100)->nullable();
            $table->string('purpose', 255);
            $table->string('name', 100);
            $table->string('email', 100)->nullable();
            $table->string('contact', 12);
            $table->string('id_proof', 50);
            $table->integer('no_of_people');
            $table->date('date');
            $table->string('in_time', 20);
            $table->string('out_time', 20);
            $table->text('note');
            $table->string('image', 100)->nullable();
            $table->string('meeting_with', 20);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors_book');
    }
};