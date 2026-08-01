<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('division_name', 255);
            $table->integer('from_mark');
            $table->integer('to_mark');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `mark_divisions` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_divisions');
    }
};
