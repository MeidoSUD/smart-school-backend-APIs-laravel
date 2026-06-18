<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_authentication', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('users_id');
            $table->string('token', 255);
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->timestamp('expired_at')->useCurrent();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
            $table->index('users_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users_authentication');
    }
};
