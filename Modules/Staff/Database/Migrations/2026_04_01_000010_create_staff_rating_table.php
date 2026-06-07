<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_rating', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('staff_id');
            $table->text('comment');
            $table->integer('rate');
            $table->integer('user_id');
            $table->string('role', 255);
            $table->integer('status')->comment('0 decline, 1 Approve');
            $table->timestamp('entrydt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_rating');
    }
};
