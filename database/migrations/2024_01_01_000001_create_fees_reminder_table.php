<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_reminder', function (Blueprint $table) {
            $table->id();
            $table->integer('fees_groups_id')->index();
            $table->date('send_date');
            $table->time('send_time');
            $table->text('message');
            $table->string('status', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_reminder');
    }
};
