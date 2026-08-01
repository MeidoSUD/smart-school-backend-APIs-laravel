<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->integer('staff_id')->index();
            $table->text('message');
            $table->string('notice', 255);
            $table->integer('is_read');
            $table->boolean('from_admin');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `messages` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `messages` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
