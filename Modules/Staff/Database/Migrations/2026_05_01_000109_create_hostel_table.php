<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hostel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('hostel_name', 100)->nullable();
            $table->string('type', 50)->nullable();
            $table->text('address')->nullable();
            $table->integer('intake')->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hostel');
    }
};
