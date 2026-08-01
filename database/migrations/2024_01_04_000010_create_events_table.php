<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('event_title', 255);
            $table->date('event_start');
            $table->date('event_end');
            $table->string('event_color', 10);
            $table->text('event_description');
            $table->integer('role_id')->unsigned();
            $table->integer('is_active')->default(1);

            $table->timestamps();

            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
