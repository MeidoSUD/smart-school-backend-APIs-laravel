<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('share_upload_contents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('upload_content_id')->nullable();
            $table->integer('share_content_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_upload_contents');
    }
};
