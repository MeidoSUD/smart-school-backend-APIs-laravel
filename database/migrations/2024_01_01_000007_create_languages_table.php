<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('language', 50)->nullable();
            $table->string('short_code', 255);
            $table->string('country_code', 255);
            $table->tinyInteger('is_rtl')->default(0);
            $table->string('is_deleted', 10)->default('yes');
            $table->string('is_active', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};