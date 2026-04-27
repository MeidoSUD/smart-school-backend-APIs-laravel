<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_supplier', function (Blueprint $table) {
            $table->id();
            $table->string('item_supplier', 255);
            $table->string('phone', 255);
            $table->string('email', 255);
            $table->string('address', 255);
            $table->string('contact_person_name', 255);
            $table->string('contact_person_phone', 255);
            $table->string('contact_person_email', 255);
            $table->text('description');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_supplier');
    }
};