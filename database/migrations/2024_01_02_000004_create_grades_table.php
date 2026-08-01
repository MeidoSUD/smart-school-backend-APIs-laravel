<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->text('description');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `grades` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
