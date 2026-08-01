<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_head', function (Blueprint $table) {
            $table->id();
            $table->string('income_head', 100);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `income_head` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('income_head');
    }
};
