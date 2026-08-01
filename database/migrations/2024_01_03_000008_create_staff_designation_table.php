<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_designation', function (Blueprint $table) {
            $table->id();
            $table->string('staff_designation_name', 100);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `staff_designation` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `staff_designation` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_designation');
    }
};
