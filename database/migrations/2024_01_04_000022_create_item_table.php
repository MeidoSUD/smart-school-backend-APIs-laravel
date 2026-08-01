<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('item_name', 255);
            $table->integer('item_category_id')->unsigned();
            $table->integer('item_store_id')->unsigned();
            $table->integer('item_supplier_id')->unsigned();
            $table->integer('quantity');
            $table->text('description');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('item_category_id');
            $table->index('item_store_id');
            $table->index('item_supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item');
    }
};
