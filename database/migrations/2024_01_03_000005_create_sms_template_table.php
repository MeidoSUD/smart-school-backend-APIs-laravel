<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_template', function (Blueprint $table) {
            $table->id();
            $table->integer('sms_template_id');
            $table->string('sms_text', 255);
            $table->string('sms_type', 10);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `sms_template` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `sms_template` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_template');
    }
};
