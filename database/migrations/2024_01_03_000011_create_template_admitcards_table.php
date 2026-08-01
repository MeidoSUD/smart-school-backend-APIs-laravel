<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_admitcards', function (Blueprint $table) {
            $table->id();
            $table->string('template_admitcard_name', 100);
            $table->text('body');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `template_admitcards` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `template_admitcards` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('template_admitcards');
    }
};
