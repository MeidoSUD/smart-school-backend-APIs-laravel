<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('send_notification', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->nullable();
            $table->date('publish_date')->nullable();
            $table->date('date')->nullable();
            $table->string('attachment', 500)->nullable();
            $table->text('message')->nullable();
            $table->string('visible_student', 10)->default('no');
            $table->string('visible_staff', 10)->default('no');
            $table->string('visible_parent', 10)->default('no');
            $table->string('created_by', 60)->nullable();
            $table->integer('created_id')->nullable();
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('send_notification');
    }
};