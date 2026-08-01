<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('leave_type', 100);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->integer('days')->default(0);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `leave_types` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
