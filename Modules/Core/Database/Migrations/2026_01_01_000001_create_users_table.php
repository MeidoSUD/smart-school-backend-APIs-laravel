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
            $table->integer('user_id');
            $table->string('username', 50)->nullable();
            $table->string('password', 50)->nullable();
            $table->text('childs');
            $table->string('role', 30);
            $table->integer('lang_id');
            $table->integer('currency_id')->nullable()->default(0);
            $table->string('verification_code', 200);
            $table->string('is_active', 255)->nullable()->default('yes');
            $table->timestamp('created_at')->useCurrent();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
