<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors_purpose', function (Blueprint $table) {
            $table->id();
            $table->string('visitors_purpose', 255);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `visitors_purpose` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `visitors_purpose` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors_purpose');
    }
};
