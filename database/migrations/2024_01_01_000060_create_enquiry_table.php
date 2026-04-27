<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('contact', 20);
            $table->text('address');
            $table->string('reference', 20);
            $table->date('date');
            $table->string('description', 500);
            $table->date('follow_up_date');
            $table->text('note');
            $table->string('source', 50);
            $table->string('email', 50)->nullable();
            $table->integer('assigned')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->string('no_of_child', 11)->nullable();
            $table->string('status', 100);
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry');
    }
};