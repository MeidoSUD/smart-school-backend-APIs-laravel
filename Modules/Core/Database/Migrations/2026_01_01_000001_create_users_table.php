<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('username', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->text('childs');
            $table->string('role', 30);
            $table->unsignedBigInteger('lang_id');
            $table->unsignedBigInteger('currency_id')->nullable()->default(0);
            $table->string('verification_code', 200);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
            $table->index('user_id');
            $table->index('role');
            $table->index('is_active');
            $table->index('username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
