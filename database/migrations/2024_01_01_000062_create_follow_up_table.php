<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follow_up', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enquiry_id');
            $table->date('date');
            $table->date('next_date');
            $table->text('response');
            $table->text('note');
            $table->unsignedBigInteger('followup_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follow_up');
    }
};