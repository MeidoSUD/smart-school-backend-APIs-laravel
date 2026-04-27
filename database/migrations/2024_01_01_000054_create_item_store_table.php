<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_store', function (Blueprint $table) {
            $table->id();
            $table->string('item_store', 255);
            $table->string('code', 255);
            $table->text('description');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_store');
    }
};