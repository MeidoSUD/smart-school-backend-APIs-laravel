<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('source', 100);
            $table->text('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source');
    }
};
