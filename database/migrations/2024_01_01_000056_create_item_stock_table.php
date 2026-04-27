<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_stock', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('symbol', 10)->default('+');
            $table->integer('quantity')->nullable();
            $table->float('purchase_price');
            $table->date('date');
            $table->string('attachment', 250)->nullable();
            $table->text('description');
            $table->string('is_active', 10)->default('yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_stock');
    }
};