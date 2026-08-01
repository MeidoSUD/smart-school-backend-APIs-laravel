<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_session_groups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('fee_groups_id')->unsigned();
            $table->integer('session_id')->unsigned();
            $table->text('description');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('fee_groups_id');
            $table->index('session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_session_groups');
    }
};
