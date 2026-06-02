<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feetype', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('is_system')->default(0);
            $table->integer('feecategory_id')->nullable();
            $table->string('type', 50)->nullable();
            $table->string('code', 100);
            $table->string('is_active', 255)->nullable()->default('no');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feetype');
    }
};
