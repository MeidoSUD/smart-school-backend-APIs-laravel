<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('front_cms_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('theme', 50)->nullable();
            $table->integer('is_active_rtl')->nullable()->default(0);
            $table->integer('is_active_front_cms')->nullable()->default(0);
            $table->integer('is_active_sidebar')->nullable()->default(0);
            $table->string('logo', 200)->nullable();
            $table->string('contact_us_email', 100)->nullable();
            $table->string('complain_form_email', 100)->nullable();
            $table->text('sidebar_options');
            $table->string('whatsapp_url', 255);
            $table->string('fb_url', 200);
            $table->string('twitter_url', 200);
            $table->string('youtube_url', 200);
            $table->string('google_plus', 200);
            $table->string('instagram_url', 200);
            $table->string('pinterest_url', 200);
            $table->string('linkedin_url', 200);
            $table->text('google_analytics')->nullable();
            $table->string('footer_text', 500)->nullable();
            $table->string('cookie_consent', 255);
            $table->string('fav_icon', 250)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('front_cms_settings');
    }
};
