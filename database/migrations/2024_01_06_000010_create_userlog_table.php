<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('userlog', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('user_id');
            $table->string('user_type', 50);
            $table->integer('class_section_id');
            $table->string('ip_address', 45);
            $table->string('agent', 255);
            $table->text('old_values');
            $table->text('new_values');
            $table->string('url', 255);
            $table->timestamps();
            $table->index('user_id');
            $table->index('class_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userlog');
    }
};
