<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('libarary_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('library_card_no', 50)->nullable();
            $table->string('member_type', 50)->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('is_active', 10)->default('no');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('libarary_members');
    }
};
