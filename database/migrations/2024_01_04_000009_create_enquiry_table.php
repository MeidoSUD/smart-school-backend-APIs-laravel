<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiry', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';

            $table->id();
            $table->string('name', 100);
            $table->string('contact', 20);
            $table->integer('enquiry_type_id')->unsigned();
            $table->integer('reference_id')->unsigned();
            $table->integer('assigned')->unsigned();
            $table->string('source', 100);
            $table->date('date');
            $table->string('response', 100);
            $table->text('note');
            $table->date('follow_up_date');
            $table->enum('is_active', ['active', 'inactive'])->default('active');

            $table->timestamps();

            $table->index('enquiry_type_id');
            $table->index('reference_id');
            $table->index('assigned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiry');
    }
};
