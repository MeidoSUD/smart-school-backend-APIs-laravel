<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mark_divisions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable();
            $table->float('percentage_from')->nullable();
            $table->float('percentage_to')->nullable();
            $table->integer('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mark_divisions');
    }
};