<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transport_feemaster', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->id();
            $table->integer('session_id');
            $table->integer('route_id');
            $table->decimal('amount', 15, 2);
            $table->enum('is_active', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index('session_id');
            $table->index('route_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_feemaster');
    }
};
