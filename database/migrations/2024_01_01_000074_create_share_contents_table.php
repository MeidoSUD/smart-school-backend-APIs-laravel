<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_contents', function (Blueprint $table) {
            $table->id();
            $table->string('send_to', 50)->nullable();
            $table->text('title')->nullable();
            $table->date('share_date')->nullable();
            $table->date('valid_upto')->nullable();
            $table->text('description')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_contents');
    }
};