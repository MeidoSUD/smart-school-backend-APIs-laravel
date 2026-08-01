<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_id_card', function (Blueprint $table) {
            $table->id();
            $table->string('id_card_name', 200);
            $table->integer('staff_id');
            $table->boolean('staff_name')->default(true);
            $table->boolean('staff_id_no')->default(true);
            $table->boolean('staff_designation')->default(true);
            $table->boolean('staff_department')->default(true);
            $table->boolean('staff_phone')->default(true);
            $table->boolean('staff_photo')->default(true);
            $table->date('valid_from');
            $table->date('valid_upto');
            $table->string('background_image', 255);
            $table->string('staff_photo_dimension', 10);
            $table->integer('header_height');
            $table->integer('content_height');
            $table->integer('footer_height');
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index('staff_id', 'staff_id_card_staff_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_id_card');
    }
};
