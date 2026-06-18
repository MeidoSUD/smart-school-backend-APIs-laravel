<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feemasters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('session_id')->nullable();
            $table->unsignedBigInteger('feetype_id');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->float('amount', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });

        Schema::table('feemasters', function (Blueprint $table) {
            $table->index('class_id');
            $table->index('feetype_id');
            $table->index('session_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feemasters');
    }
};
