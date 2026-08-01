<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('income_head_id')->unsigned();
            $table->string('name', 255);
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->string('document', 255);
            $table->text('note');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('income_head_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income');
    }
};
