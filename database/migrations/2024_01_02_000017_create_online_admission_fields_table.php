<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name', 100);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `online_admission_fields` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_fields');
    }
};
