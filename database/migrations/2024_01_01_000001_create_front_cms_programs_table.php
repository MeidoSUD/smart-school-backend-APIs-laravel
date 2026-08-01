<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('summary');
            $table->text('description');
            $table->string('image', 255);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('venue', 255);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('type', ['public', 'private'])->default('public');
            $table->string('slug', 255);
            $table->integer('date_added');
            $table->string('user_type', 50);
            $table->integer('user_id')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_programs');
    }
};
