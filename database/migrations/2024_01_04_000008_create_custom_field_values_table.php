<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('custom_fields_id')->unsigned();
            $table->string('belong_to', 50);
            $table->integer('belong_id')->unsigned();
            $table->string('value', 255);

            $table->timestamps();

            $table->index('custom_fields_id');
            $table->index('belong_to');
            $table->index('belong_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
    }
};
