<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('id_card', function (Blueprint $table) {
            $table->id();
            $table->string('id_card_name', 200);
            $table->string('record_for', 20);
            $table->boolean('library_card_no')->default(1);
            $table->boolean('admission_no')->default(1);
            $table->boolean('student_name')->default(1);
            $table->boolean('class')->default(1);
            $table->boolean('section')->default(1);
            $table->boolean('father_name')->default(1);
            $table->boolean('student_address')->default(1);
            $table->boolean('blood_group')->default(1);
            $table->boolean('student_phone')->default(1);
            $table->boolean('student_photo')->default(1);
            $table->date('valid_from');
            $table->date('valid_upto');
            $table->string('background_image', 255);
            $table->string('student_photo_dimension', 10);
            $table->integer('header_height');
            $table->integer('content_height');
            $table->integer('footer_height');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `id_card` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('id_card');
    }
};
