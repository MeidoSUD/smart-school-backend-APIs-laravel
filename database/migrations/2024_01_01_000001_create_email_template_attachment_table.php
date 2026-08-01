<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_attachment', function (Blueprint $table) {
            $table->id();
            $table->integer('email_template_id')->index();
            $table->string('attachment', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_attachment');
    }
};
