<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sch_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('session_id');
            $table->integer('lang_id');
            $table->string('name', 100);
            $table->text('address');
            $table->string('phone_no', 20);
            $table->string('email', 100);
            $table->integer('currency_id');
            $table->string('currency_symbol', 10);
            $table->string('logo', 255);
            $table->string('favicon', 255);
            $table->string('time_zone', 100);
            $table->string('date_format', 20);
            $table->string('time_format', 20);
            $table->enum('is_rtl', ['0', '1'])->default('0');
            $table->string('theme', 50);
            $table->timestamps();
            $table->index('session_id', 'sch_settings_session_id_index');
            $table->index('lang_id', 'sch_settings_lang_id_index');
            $table->index('currency_id', 'sch_settings_currency_id_index');
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sch_settings');
    }
};
