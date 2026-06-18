<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_head', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('exp_category', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('is_active', 255)->nullable()->default('yes');
            $table->string('is_deleted', 255)->nullable()->default('no');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_head');
    }
};
