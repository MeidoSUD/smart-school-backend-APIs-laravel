<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_category_id')->nullable();
            $table->unsignedBigInteger('item_store_id')->nullable();
            $table->unsignedBigInteger('item_supplier_id')->nullable();
            $table->string('name', 255);
            $table->string('unit', 100);
            $table->string('item_photo', 225)->nullable();
            $table->text('description');
            $table->integer('quantity');
            $table->date('date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};