<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_contents', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->unsigned()->index();
            $table->integer('content_type_id')->unsigned()->index();
            $table->string('title', 255);
            $table->text('description');
            $table->string('upload_content', 255);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_contents');
    }
};
