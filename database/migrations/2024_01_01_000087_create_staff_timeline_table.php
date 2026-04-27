<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timeline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->string('title', 200);
            $table->date('timeline_date');
            $table->string('description', 300);
            $table->string('document', 200);
            $table->string('status', 200);
            $table->date('date');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timeline');
    }
};