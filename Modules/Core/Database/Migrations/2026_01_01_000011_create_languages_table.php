<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('language', 50)->nullable();
            $table->string('short_code', 255);
            $table->string('country_code', 255);
            $table->integer('is_rtl');
            $table->string('is_deleted', 10)->default('yes');
            $table->string('is_active', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
