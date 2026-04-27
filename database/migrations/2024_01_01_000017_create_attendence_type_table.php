<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendence_type', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->nullable();
            $table->string('key_value', 50);
            $table->string('long_lang_name', 250)->nullable();
            $table->string('long_name_style', 250)->nullable();
            $table->string('is_active', 255)->default('no');
            $table->integer('for_qr_attendance')->default(1);
            $table->timestamps();
            $table->date('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendence_type');
    }
};