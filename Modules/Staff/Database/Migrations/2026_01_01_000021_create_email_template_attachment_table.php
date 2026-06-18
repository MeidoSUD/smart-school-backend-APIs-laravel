<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_attachment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('email_template_id');
            $table->string('attachment', 100);
            $table->text('attachment_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_attachment');
    }
};
