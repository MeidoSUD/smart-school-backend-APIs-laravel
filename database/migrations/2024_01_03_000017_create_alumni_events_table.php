<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_heading', 255);
            $table->date('event_date');
            $table->text('event_description');
            $table->string('event_location', 255);
            $table->string('event_image', 255);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `alumni_events` ENGINE = InnoDB');
        DB::statement('ALTER TABLE `alumni_events` DEFAULT CHARSET = utf8mb4');
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_events');
    }
};
