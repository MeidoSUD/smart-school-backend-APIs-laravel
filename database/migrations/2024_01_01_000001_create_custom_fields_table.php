<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('form_type', 11);
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
