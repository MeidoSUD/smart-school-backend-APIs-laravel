<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_timeline', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->string('title', 255);
            $table->text('description');
            $table->date('date');
            $table->string('document', 255);

            $table->timestamps();

            $table->index('staff_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_timeline');
    }
};
