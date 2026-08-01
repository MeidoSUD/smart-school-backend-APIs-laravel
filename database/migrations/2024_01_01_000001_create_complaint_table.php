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
            $table->integer('complaint_type_id');
            $table->string('complaint_by', 100);
            $table->date('complaint_date');
            $table->string('description', 100);
            $table->string('action_taken', 100);
            $table->string('assigned', 100);
            $table->string('status', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint');
    }
};
