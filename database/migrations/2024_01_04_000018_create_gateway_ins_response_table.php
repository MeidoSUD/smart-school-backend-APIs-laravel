<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_ins_response', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->integer('gateway_ins_id')->unsigned();
            $table->string('transaction_id', 255);
            $table->string('response_code', 100);
            $table->text('message');

            $table->timestamps();

            $table->index('gateway_ins_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_ins_response');
    }
};
