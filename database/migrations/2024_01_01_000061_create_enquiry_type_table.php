<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry_type', function (Blueprint $table) {
            $table->id();
            $table->string('enquiry_type', 100);
            $table->text('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry_type');
    }
};