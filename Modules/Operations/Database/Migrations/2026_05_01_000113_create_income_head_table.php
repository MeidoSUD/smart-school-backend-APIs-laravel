<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_head', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('income_category', 255)->nullable();
            $table->string('description', 255)->nullable();
            $table->string('is_active', 255)->default('yes');
            $table->string('is_deleted', 255)->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_head');
    }
};
