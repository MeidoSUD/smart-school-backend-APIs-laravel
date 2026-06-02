<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors_purpose', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('visitors_purpose', 100);
            $table->text('description');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors_purpose');
    }
};
