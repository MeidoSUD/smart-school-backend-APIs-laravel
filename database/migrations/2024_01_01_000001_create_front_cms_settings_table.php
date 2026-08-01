<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_settings', function (Blueprint $table) {
            $table->id();
            $table->string('from_email', 50);
            $table->string('from_name', 50);
            $table->string('reply_to_email', 50);
            $table->string('reply_to_name', 50);
            $table->string('admin_email', 100);
            $table->string('admin_name', 100);
            $table->string('footer_text', 255);
            $table->text('address');
            $table->string('phone', 20);
            $table->string('logo', 255);
            $table->string('favicon', 255);
            $table->string('theme_color', 10);
            $table->string('meta_title', 255);
            $table->text('meta_description');
            $table->text('meta_keyword');
            $table->enum('smtp_enable', ['0', '1'])->default('1');
            $table->string('smtp_host', 255);
            $table->integer('smtp_port');
            $table->string('smtp_user', 255);
            $table->string('smtp_pass', 255);
            $table->string('smtp_encryption', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_settings');
    }
};
