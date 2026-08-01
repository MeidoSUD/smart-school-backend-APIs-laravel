<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feemasters', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('session_id');
            $table->integer('feetype_id');
            $table->integer('class_id');
            $table->string('feemaster_name', 100);
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('due_date');
            $table->enum('fine_type', ['fixed', 'percentage']);
            $table->decimal('fine_amount', 15, 2);
            $table->decimal('fine_percentage', 5, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('session_id');
            $table->index('feetype_id');
            $table->index('class_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feemasters');
    }
};
