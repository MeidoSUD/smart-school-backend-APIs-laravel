<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_session_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('fee_groups_id')->nullable();
            $table->integer('session_id')->nullable();
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_session_groups');
    }
};
