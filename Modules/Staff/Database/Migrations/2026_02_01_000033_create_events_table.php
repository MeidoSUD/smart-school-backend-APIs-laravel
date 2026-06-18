<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_title', 200);
            $table->text('event_description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('event_type', 100);
            $table->string('event_color', 200);
            $table->string('event_for', 100);
            $table->unsignedBigInteger('role_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->index('is_active');
            $table->index('event_for');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
