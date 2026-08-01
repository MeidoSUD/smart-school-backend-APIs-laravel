<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel', function (Blueprint $table) {
            $table->id();
            $table->string('hostel_name', 100);
            $table->string('hostel_type', 10);
            $table->text('address');
            $table->string('contact_no', 20);
            $table->enum('is_active', ['y', 'n'])->default('y');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `hostel` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel');
    }
};
