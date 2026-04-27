<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feetype', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('is_system')->default(0);
            $table->unsignedBigInteger('feecategory_id')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('code', 100);
            $table->string('is_active', 255)->default('no');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feetype');
    }
};