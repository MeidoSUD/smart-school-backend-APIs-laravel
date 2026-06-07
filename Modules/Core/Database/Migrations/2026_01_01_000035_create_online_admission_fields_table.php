<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_admission_fields', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 250)->nullable();
            $table->integer('status')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_admission_fields');
    }
};
