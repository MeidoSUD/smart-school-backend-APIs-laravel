<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_head', function (Blueprint $table) {
            $table->id();
            $table->string('expense_head', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_head');
    }
};
