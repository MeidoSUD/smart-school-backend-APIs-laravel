<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier', 100);
            $table->text('description');
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `item_supplier` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('item_supplier');
    }
};
