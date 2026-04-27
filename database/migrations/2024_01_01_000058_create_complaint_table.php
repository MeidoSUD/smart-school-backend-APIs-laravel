<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_type', 255);
            $table->string('source', 255);
            $table->string('name', 100);
            $table->string('contact', 15);
            $table->string('email', 200);
            $table->date('date');
            $table->text('description');
            $table->string('action_taken', 200);
            $table->string('assigned', 50);
            $table->text('note');
            $table->string('image', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint');
    }
};