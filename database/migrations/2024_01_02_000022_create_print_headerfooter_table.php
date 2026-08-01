<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_headerfooter', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('for', 50);
            $table->longText('header_left');
            $table->longText('header_right');
            $table->longText('footer_left');
            $table->longText('footer_right');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `print_headerfooter` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('print_headerfooter');
    }
};
