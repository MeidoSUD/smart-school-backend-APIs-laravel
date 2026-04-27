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
            $table->string('exp_category', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 255)->default('yes');
            $table->string('is_deleted', 255)->default('no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_head');
    }
};