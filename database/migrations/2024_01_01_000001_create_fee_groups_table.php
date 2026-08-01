<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_groups', function (Blueprint $table) {
            $table->id();
            $table->string('fee_group_name', 100);
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_groups');
    }
};
