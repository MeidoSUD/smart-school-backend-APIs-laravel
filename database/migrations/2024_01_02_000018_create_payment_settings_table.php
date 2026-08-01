<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method', 50);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `payment_settings` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
