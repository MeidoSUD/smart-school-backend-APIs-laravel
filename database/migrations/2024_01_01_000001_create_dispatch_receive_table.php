<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatch_receive', function (Blueprint $table) {
            $table->id();
            $table->string('from_to', 255);
            $table->string('reference_no', 100);
            $table->string('address', 255);
            $table->text('note');
            $table->string('document', 255);
            $table->string('dispatch_receive', 10);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatch_receive');
    }
};
