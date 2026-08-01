<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_program_photos', function (Blueprint $table) {
            $table->id();
            $table->integer('program_id');
            $table->string('photo', 255);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `front_cms_program_photos` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_program_photos');
    }
};
