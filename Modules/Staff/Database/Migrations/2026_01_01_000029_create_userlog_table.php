<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('userlog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user', 100)->nullable();
            $table->string('role', 100)->nullable();
            $table->unsignedBigInteger('class_section_id')->nullable();
            $table->string('ipaddress', 100)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('login_datetime')->useCurrent();
            $table->index('class_section_id');
            $table->index('user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('userlog');
    }
};
