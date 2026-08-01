<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_rating', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('rating_by');
            $table->string('rating', 20);
            $table->text('comment');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('rating_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_rating');
    }
};
