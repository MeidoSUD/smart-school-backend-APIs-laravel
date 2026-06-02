<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_headerfooter', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('print_type', 255);
            $table->string('header_image', 255);
            $table->text('footer_content');
            $table->integer('created_by');
            $table->timestamp('entry_date')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_headerfooter');
    }
};
