<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libarary_members', function (Blueprint $table) {
            $table->id();
            $table->integer('library_card_no');
            $table->string('member_type', 20);
            $table->integer('member_id');
            $table->enum('is_active', ['yes', 'no'])->default('yes');
            $table->timestamps();
        });

        DB::statement('ALTER TABLE `libarary_members` ENGINE = InnoDB');
    }

    public function down(): void
    {
        Schema::dropIfExists('libarary_members');
    }
};
