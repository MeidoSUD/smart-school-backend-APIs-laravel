<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_type', 10);
            $table->string('model_name', 20);
            $table->integer('model_id');
            $table->string('user_type', 50);
            $table->integer('user_id');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `general_calls` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('general_calls');
    }
};
