<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('exam_type', 250)->nullable();
            $table->string('name', 100)->nullable();
            $table->float('point')->nullable();
            $table->float('mark_from')->nullable();
            $table->float('mark_upto')->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};