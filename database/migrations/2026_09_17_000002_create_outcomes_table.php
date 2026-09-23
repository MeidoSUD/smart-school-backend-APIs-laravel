<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stander_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->tinyInteger('level')->unsigned()->default(1);
            $table->boolean('status')->default(0);
            $table->unsignedBigInteger('session_id')->nullable();
            $table->timestamps();

            $table->foreign('stander_id')->references('id')->on('standards')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outcomes');
    }
};
