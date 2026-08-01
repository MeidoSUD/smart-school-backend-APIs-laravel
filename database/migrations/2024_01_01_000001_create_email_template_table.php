<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template', function (Blueprint $table) {
            $table->id();
            $table->string('template_for', 100);
            $table->string('template_name', 255);
            $table->string('subject', 255);
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template');
    }
};
