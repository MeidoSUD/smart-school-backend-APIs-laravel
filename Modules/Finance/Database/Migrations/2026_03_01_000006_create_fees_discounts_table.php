<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fees_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('session_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('code', 100)->nullable();
            $table->string('type', 20)->nullable();
            $table->float('percentage', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fees_discounts');
    }
};
