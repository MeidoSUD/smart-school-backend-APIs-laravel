<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_category', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_category', 255);
            $table->string('is_active', 255)->default('yes');
            $table->string('description', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_category');
    }
};
