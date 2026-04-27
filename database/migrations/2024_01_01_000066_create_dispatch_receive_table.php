<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatch_receive', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 50);
            $table->string('to_title', 100);
            $table->string('type', 10);
            $table->string('address', 500);
            $table->string('note', 500);
            $table->string('from_title', 200);
            $table->date('date')->nullable();
            $table->string('image', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_receive');
    }
};