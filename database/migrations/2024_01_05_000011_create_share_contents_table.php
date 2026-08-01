<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_contents', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('staff_id');
            $table->integer('content_id');
            $table->integer('share_user_id');
            $table->string('share_user_type', 50);
            $table->text('description');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('staff_id');
            $table->index('content_id');
            $table->index('share_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_contents');
    }
};
