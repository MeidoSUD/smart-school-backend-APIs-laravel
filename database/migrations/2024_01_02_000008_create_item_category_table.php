<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_category', function (Blueprint $table) {
            $table->id();
            $table->string('category', 100);
            $table->text('description');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `item_category` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('item_category');
    }
};
