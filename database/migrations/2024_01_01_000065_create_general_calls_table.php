<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_calls', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('contact', 12);
            $table->date('date');
            $table->string('description', 500);
            $table->date('follow_up_date');
            $table->string('call_duration', 50);
            $table->text('note');
            $table->string('call_type', 20);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_calls');
    }
};