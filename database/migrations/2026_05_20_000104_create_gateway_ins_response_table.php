<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_ins_response', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('gateway_ins_id')->nullable();
            $table->text('posted_data')->nullable();
            $table->text('response')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_ins_response');
    }
};
