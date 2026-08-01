<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_custom_field_value', function (Blueprint $table) {
            $table->id();
            $table->integer('custom_fields_id');
            $table->integer('online_admission_id');
            $table->string('value', 255);
            $table->timestamps();
            $table->index('custom_fields_id', 'online_admission_custom_field_value_custom_fields_id_index');
            $table->index('online_admission_id', 'online_admission_custom_field_value_online_admission_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_custom_field_value');
    }
};
