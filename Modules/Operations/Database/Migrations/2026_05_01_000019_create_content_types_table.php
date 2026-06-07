<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 200)->nullable();
            $table->text('description')->nullable();
            $table->integer('is_active')->nullable()->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_types');
    }
};
