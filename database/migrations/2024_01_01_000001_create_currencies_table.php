<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_name', 100);
            $table->string('currency_icon', 10);
            $table->string('currency_code', 10);
            $table->integer('no_of_decimal')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
